<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\LeaveStarSystem;

use Fleet;
use request;
use ShipData;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use SystemActivationWrapper;

final class LeaveStarSystem implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_LEAVE_STARSYSTEM';

    private $shipLoader;

    public function __construct(
        ShipLoaderInterface $shipLoader
    ) {
        $this->shipLoader = $shipLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        if (!$ship->isInSystem()) {
            return;
        }
        $wrapper = new SystemActivationWrapper($ship);
        $wrapper->setVar('eps', 1);
        if ($wrapper->getError()) {
            $game->addInformation($wrapper->getError());
            return;
        }
        $ship->cancelRepair();
        $ship->leaveStarSystem();
        if ($ship->isTraktorbeamActive()) {
            $this->leaveStarSystemTraktor($ship, $game);
        }
        if ($ship->isFleetLeader()) {
            $msg = array();
            $result = Fleet::getShipsBy($ship->getFleetId(), array($ship->getId()));
            foreach ($result as $key => $ship) {
                $wrapper = new SystemActivationWrapper($ship);
                $wrapper->setVar('eps', 1);
                if ($wrapper->getError()) {
                    $msg[] = "Die " . $ship->getName() . " hat die Flotte verlassen. Grund: " . $wrapper->getError();
                    $ship->leaveFleet();
                } else {
                    $ship->leaveStarSystem();
                    if ($ship->isTraktorbeamActive()) {
                        $this->leaveStarSystemTraktor($ship, $game);
                    }
                }
                $ship->save();
            }
            $game->addInformation("Die Flotte hat das Sternensystem verlassen");
            $game->addInformationMerge($msg);
        } else {
            if ($ship->isInFleet()) {
                $ship->leaveFleet();
                $game->addInformation("Das Schiff hat die Flotte verlassen");
            }
            $game->addInformation("Das Sternensystem wurde verlassen");
        }
        $ship->save();
    }

    private function leaveStarSystemTraktor(ShipData $ship, GameControllerInterface $game): void
    {
        if ($ship->getEps() < 1) {
            $ship->getTraktorShip()->unsetTraktor();
            $ship->getTraktorShip()->save();
            $ship->unsetTraktor();
            $game->addInformation("Der Traktorstrahl auf die " . $ship->getTraktorShip()->getName() . " wurde beim Verlassen des Systems aufgrund Energiemangels deaktiviert");
            return;
        }
        $ship->getTraktorShip()->leaveStarSystem();
        $ship->lowerEps(1);
        $ship->getTraktorShip()->save();
        $ship->save();
        $game->addInformation("Die " . $ship->getTraktorShip()->getName() . " wurde mit aus dem System gezogen");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
