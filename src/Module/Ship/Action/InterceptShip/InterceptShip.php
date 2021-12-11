<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\InterceptShip;

use request;
use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\PositionCheckerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Component\Ship\System\Exception\AlreadyOffException;
use Stu\Module\Ship\Lib\AlertRedHelperInterface;

final class InterceptShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_INTERCEPT';

    private ShipLoaderInterface $shipLoader;

    private PrivateMessageSenderInterface $privateMessageSender;

    private ShipSystemManagerInterface $shipSystemManager;

    private PositionCheckerInterface $positionChecker;

    private AlertRedHelperInterface $alertRedHelper;

    private EntityManagerInterface $entityManager;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        PrivateMessageSenderInterface $privateMessageSender,
        ShipSystemManagerInterface $shipSystemManager,
        PositionCheckerInterface $positionChecker,
        AlertRedHelperInterface $alertRedHelper,
        EntityManagerInterface $entityManager
    ) {
        $this->shipLoader = $shipLoader;
        $this->privateMessageSender = $privateMessageSender;
        $this->shipSystemManager = $shipSystemManager;
        $this->positionChecker = $positionChecker;
        $this->alertRedHelper = $alertRedHelper;
        $this->entityManager = $entityManager;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $target = $this->shipLoader->find(request::indInt('target'));
        if ($target === null) {
            return;
        }
        if (!$this->positionChecker->checkPosition($target, $ship)) {
            return;
        }

        if (!$ship->hasEnoughCrew()) {
            $game->addInformationf(
                _("Es werden %d Crewmitglieder benötigt"),
                $ship->getBuildplan()->getCrew()
            );
            return;
        }

        if (!$target->getWarpState()) {
            return;
        }
        if (!$ship->canIntercept()) {
            return;
        }
        if ($ship->getDockedTo()) {
            $game->addInformation('Das Schiff hat abgedockt');
            $ship->setDockedTo(null);
        }
        if ($target->getFleetId()) {
            foreach ($target->getFleet()->getShips() as $fleetShip) {
                try {
                    $this->shipSystemManager->deactivate($fleetShip, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
                } catch (AlreadyOffException $e) {
                }
                $this->shipLoader->save($fleetShip);
            }

            $game->addInformation("Die Flotte " . $target->getFleet()->getName() . " wurde abgefangen");
            $pm = "Die Flotte " . $target->getFleet()->getName() . " wurde von der " . $ship->getName() . " abgefangen";
        } else {
            $this->shipSystemManager->deactivate($target, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);

            $game->addInformation("Die " . $target->getName() . "  wurde abgefangen");
            $pm = "Die " . $target->getName() . " wurde von der " . $ship->getName() . " abgefangen";

            $this->shipLoader->save($target);
        }

        $href = sprintf(_('ship.php?SHOW_SHIP=1&id=%d'), $target->getId());

        $this->privateMessageSender->send(
            $userId,
            (int) $target->getUser()->getId(),
            $pm,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
            $href
        );
        $interceptorLeftWarp = false;
        if ($ship->getFleetId()) {
            foreach ($ship->getFleet()->getShips() as $fleetShip) {
                try {
                    $this->shipSystemManager->deactivate($fleetShip, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
                    $interceptorLeftWarp = true;
                } catch (AlreadyOffException $e) {
                }
                $this->shipLoader->save($fleetShip);
            }
        } else {
            try {
                $this->shipSystemManager->deactivate($ship, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
                $interceptorLeftWarp = true;
            } catch (AlreadyOffException $e) {
            }

            $this->shipLoader->save($ship);
        }
        $this->entityManager->flush();

        //Alert red check for the target(s)
        $informations = [];
        $shipsToShuffle = $this->alertRedHelper->checkForAlertRedShips($target, $informations);
        shuffle($shipsToShuffle);
        foreach ($shipsToShuffle as $alertShip) {
            $this->alertRedHelper->performAttackCycle($alertShip, $target, $informations);
        }

        //Alert red check for the interceptor(s)
        $informations = [];
        if ($interceptorLeftWarp) {
            $shipsToShuffle = $this->alertRedHelper->checkForAlertRedShips($ship, $informations);
            shuffle($shipsToShuffle);
            foreach ($shipsToShuffle as $alertShip) {
                $this->alertRedHelper->performAttackCycle($alertShip, $ship, $informations);
            }
            $game->addInformationMergeDown($informations);
        }
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
