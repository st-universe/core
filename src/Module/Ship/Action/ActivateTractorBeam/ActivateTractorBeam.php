<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\ActivateTractorBeam;

use request;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\PositionCheckerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipAttackCycleInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;

final class ActivateTractorBeam implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ACTIVATE_TRAKTOR';

    private ShipLoaderInterface $shipLoader;

    private PrivateMessageSenderInterface $privateMessageSender;

    private ShipRepositoryInterface $shipRepository;

    private ShipAttackCycleInterface $shipAttackCycle;

    private PositionCheckerInterface $positionChecker;

    private ActivatorDeactivatorHelperInterface $helper;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        PrivateMessageSenderInterface $privateMessageSender,
        ShipRepositoryInterface $shipRepository,
        ShipAttackCycleInterface $shipAttackCycle,
        PositionCheckerInterface $positionChecker,
        ActivatorDeactivatorHelperInterface $helper
    ) {
        $this->shipLoader = $shipLoader;
        $this->privateMessageSender = $privateMessageSender;
        $this->shipRepository = $shipRepository;
        $this->shipAttackCycle = $shipAttackCycle;
        $this->positionChecker = $positionChecker;
        $this->helper = $helper;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $shipName = $ship->getName();

        $target = $this->shipRepository->find(request::getIntFatal('target'));
        if ($target === null) {
            return;
        }
        if (!$this->positionChecker->checkPosition($ship, $target)) {
            return;
        }
        if ($target->getUser()->isVacationRequestOldEnough()) {
            $game->addInformation(_('Aktion nicht möglich, der Spieler befindet sich im Urlaubsmodus!'));
            $game->setView(ShowShip::VIEW_IDENTIFIER);
            return;
        }

        $targetName = $target->getName();

        // activate system
        if (!$this->helper->activate(request::indInt('id'), ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, $game)) {
            $game->setView(ShowShip::VIEW_IDENTIFIER);
            return;
        }

        if ($target->getRump()->isTrumfield()) {
            $game->addInformation("Das Trümmerfeld kann nicht erfasst werden");
            $this->abort($ship, $game);
            return;
        }
        if ($target->isBase()) {
            $game->addInformation("Die " . $targetName . " kann nicht erfasst werden");
            $this->abort($ship, $game);
            return;
        }
        if ($target->traktorbeamToShip()) {
            $game->addInformation("Das Schiff wird bereits vom Traktorstrahl der " . $target->getTraktorShip()->getName() . " gehalten");
            $this->abort($ship, $game);
            return;
        }
        if ($target->getFleetId() && $target->getFleetId() == $ship->getFleetId()) {
            $game->addInformation("Die " . $targetName . " befindet sich in der selben Flotte wie die " . $shipName);
            $this->abort($ship, $game);
            return;
        }
        if (($target->getAlertState() == ShipAlertStateEnum::ALERT_YELLOW || $target->getAlertState() == ShipAlertStateEnum::ALERT_RED)
            && !$target->getUser()->isFriend($userId)
            && $target->getUser()->getId() !== $userId
        ) {
            $defender = [$ship->getId() => $ship];

            if ($target->getFleetId()) {
                $attacker = $target->getFleet()->getShips()->toArray();
            } else {
                $attacker = [$target->getId() => $target];
            }

            $this->shipAttackCycle->init($attacker, $defender, true);
            try {
                $this->shipAttackCycle->cycle();
            } finally {
            }
            $game->addInformationMergeDown($this->shipAttackCycle->getMessages());

            $this->privateMessageSender->send(
                $userId,
                $target->getUser()->getId(),
                sprintf(
                    "Die %s versucht die %s in Sektor %s mit dem Traktorstrahl zu erfassen. Folgende Aktionen wurden ausgeführt:\n%s",
                    $shipName,
                    $targetName,
                    $ship->getSectorString(),
                    implode(PHP_EOL, $this->shipAttackCycle->getMessages())
                ),
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
            );
        }
        if ($ship->getIsDestroyed()) {
            return;
        }
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        //is tractor beam system still healthy?
        if (!$ship->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM)) {
            $game->addInformation("Der Traktorstrahl wurde bei dem Angriff zerstört");
            return;
        }

        //is nbs system still healthy?
        if (!$ship->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_NBS)) {
            $game->addInformation("Abbruch, die Nahbereichssensoren wurden bei dem Angriff zerstört");
            $this->abort($ship, $game);
            return;
        }


        if ($target->getShieldState()) {
            $game->addInformation("Die " . $targetName . " kann aufgrund der aktiven Schilde nicht erfasst werden");
            $this->abort($ship, $game);
            return;
        }
        $target->deactivateTraktorBeam();
        $ship->setTraktorMode(1);
        $ship->setTraktorShipId($target->getId());
        $target->setTraktorMode(2);
        $target->setTraktorShipId($ship->getId());

        $this->shipRepository->save($target);
        $this->shipRepository->save($ship);

        if ($userId != $target->getUser()->getId()) {
            $this->privateMessageSender->send(
                $userId,
                $target->getUser()->getId(),
                "Die " . $targetName . " wurde in Sektor " . $ship->getSectorString() . " vom Traktorstrahl der " . $shipName . " erfasst",
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
            );
        }
        $game->addInformation("Der Traktorstrahl wurde auf die " . $targetName . " gerichtet");
    }

    private function abort($ship, $game): void
    {
        // deactivate system
        $this->helper->deactivate(request::indInt('id'), ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, $game);
        $this->shipRepository->save($ship);

        $game->setView(ShowShip::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
