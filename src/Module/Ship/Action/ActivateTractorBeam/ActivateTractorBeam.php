<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\ActivateTractorBeam;

use request;
use ShipSingleAttackCycle;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Module\Communication\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Communication\Lib\PrivateMessageSenderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ActivateTractorBeam implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ACTIVATE_TRAKTOR';

    private $shipLoader;

    private $privateMessageSender;

    private $shipRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        PrivateMessageSenderInterface $privateMessageSender,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->privateMessageSender = $privateMessageSender;
        $this->shipRepository = $shipRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        if ($ship->isTraktorBeamActive()) {
            return;
        }
        if ($ship->getShieldState()) {
            $game->addInformation("Die Schilde sind aktiviert");
            return;
        }
        if ($ship->getDockedTo()) {
            $game->addInformation("Das Schiff ist angedockt");
            return;
        }

        if ($ship->getBuildplan()->getCrew() > 0 && $ship->getCrewCount() === 0) {
            $game->addInformation(_('Das Schiff hat keine Crew'));
            return;
        }

        $energyCosts = 2;

        if ($ship->getEps() < $energyCosts) {
            $game->addInformationf(
                _('Es wird %s Energie benötigt'),
                $energyCosts
            );
            return;
        }

        $target = $this->shipRepository->find(request::getIntFatal('target'));
        if ($target === null) {
            return;
        }
        if ($target->getRump()->isTrumfield()) {
            $game->addInformation("Das Trümmerfeld kann nicht erfasst werden");
            return;
        }
        if (!checkPosition($ship, $target)) {
            return;
        }
        if ($target->isBase()) {
            $game->addInformation("Die " . $target->getName() . " kann nicht erfasst werden");
            return;
        }
        if ($target->traktorbeamToShip()) {
            $game->addInformation("Das Schiff wird bereits vom Traktorstrahl der " . $target->getTraktorShip()->getName() . " gehalten");
            return;
        }
        if ($target->getFleetId() && $target->getFleetId() == $ship->getFleetId()) {
            $game->addInformation("Die " . $target->getName() . " befindet sich in der selben Flotte wie die " . $ship->getName());
            return;
        }
        if (($target->getAlertState() == ShipAlertStateEnum::ALERT_YELLOW || $target->getAlertState() == ShipAlertStateEnum::ALERT_RED) && !$target->getUser()->isFriend($userId)) {
            if ($target->getFleetId()) {
                $attacker = $target->getFleet()->getShips();
            } else {
                $attacker = $target;
            }
            $obj = new ShipSingleAttackCycle($attacker, $ship);
            $obj->cycle();
            $game->addInformationMergeDown($obj->getMessages());

            $this->privateMessageSender->send(
                $userId,
                (int) $target->getUserId(),
                sprintf(
                    "Die %s versucht die %s in Sektor %s mit dem Traktorstrahl zu erfassen. Folgende Aktionen wurden ausgeführt:\n%s",
                    $ship->getName(),
                    $target->getName(),
                    $ship->getSectorString(),
                    implode(PHP_EOL, $obj->getMessages())
                ),
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP);
        }
        if ($target->getShieldState()) {
            $game->addInformation("Die " . $target->getName() . " kann aufgrund der aktiven Schilde nicht erfasst werden");
            return;
        }
        $target->deactivateTraktorBeam();
        $ship->setTraktorMode(1);
        $ship->setTraktorShipId($target->getId());
        $target->setTraktorMode(2);
        $target->setTraktorShipId($ship->getId());
        $ship->setEps($ship->getEps() - $energyCosts);

        $this->shipRepository->save($target);
        $this->shipRepository->save($ship);

        if ($userId != $target->getUserId()) {
            $this->privateMessageSender->send(
                $userId,
                (int)$target->getUserId(),
                "Die " . $target->getName() . " wurde in SeKtor " . $ship->getSectorString() . " vom Traktorstrahl der " . $ship->getName() . " erfasst",
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
            );
        }
        $game->addInformation("Der Traktorstrahl wurde auf die " . $target->getName() . " gerichtet");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
