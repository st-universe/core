<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\LeaveStarSystem;

use request;
use Ship;
use ShipData;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
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
        $this->leaveStarSystem($ship);
        if ($ship->isTraktorbeamActive()) {
            $this->leaveStarSystemTraktor($ship, $game);
        }
        if ($ship->isFleetLeader()) {
            $msg = array();
            $result = Ship::getShipsBy($ship->getFleetId(), [$ship->getId()]);
            foreach ($result as $key => $fleetShip) {
                $wrapper = new SystemActivationWrapper($fleetShip);
                $wrapper->setVar('eps', 1);
                if ($wrapper->getError()) {
                    $msg[] = "Die " . $ship->getName() . " hat die Flotte verlassen. Grund: " . $wrapper->getError();
                    $ship->leaveFleet();
                } else {
                    $this->leaveStarSystem($fleetShip);
                    if ($fleetShip->isTraktorbeamActive()) {
                        $this->leaveStarSystemTraktor($fleetShip, $game);
                    }
                }
                $fleetShip->save();
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
        $this->leaveStarSystem($ship->getTraktorShip());
        $ship->lowerEps(1);
        $ship->getTraktorShip()->save();
        $ship->save();
        $game->addInformation("Die " . $ship->getTraktorShip()->getName() . " wurde mit aus dem System gezogen");
    }

    private function leaveStarSystem(ShipData $ship): void {
        $ship->setWarpState(1);
        $ship->setSystemsId(0);
        $ship->setSX(0);
        $ship->setSY(0);
    }


    public function performSessionCheck(): bool
    {
        return true;
    }
}
