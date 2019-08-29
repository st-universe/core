<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\EnterStarSystem;

use Fleet;
use request;
use ShipData;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use SystemActivationWrapper;

final class EnterStarSystem implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ENTER_STARSYSTEM';

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

        if (!$ship->isOverSystem()) {
            return;
        }
        $wrapper = new SystemActivationWrapper($ship);
        $wrapper->setVar('eps', 1);
        if ($wrapper->getError()) {
            $game->addInformation($wrapper->getError());
            return;
        }
        switch ($ship->getFlightDirection()) {
            case 1:
                $posx = rand(1, (int) $ship->getSystem()->getMaxX());
                $posy = 1;
                break;
            case 2:
                $posx = rand(1, (int) $ship->getSystem()->getMaxX());
                $posy = $ship->getSystem()->getMaxY();
                break;
            case 3:
                $posx = 1;
                $posy = rand(1, (int) $ship->getSystem()->getMaxY());
                break;
            case 4:
                $posx = $ship->getSystem()->getMaxX();
                $posy = rand(1, (int) $ship->getSystem()->getMaxY());
                break;
        }

        $system = $ship->getSystem();
        $systemId = $system->getId();

        // @todo Beschädigung bei Systemeinflug
        $ship->enterStarSystem($systemId, $posx, $posy);
        if ($ship->isTraktorbeamActive()) {
            $this->enterStarSystemTraktor($ship, $game);
        }
        if ($ship->isFleetLeader()) {
            $msg = array();
            $result = Fleet::getShipsBy($ship->getFleetId(), array($ship->getId()));
            foreach ($result as $key => $fleetShip) {
                $wrapper = new SystemActivationWrapper($fleetShip);
                $wrapper->setVar('eps', 1);
                if ($wrapper->getError()) {
                    $msg[] = "Die " . $fleetShip->getName() . " hat die Flotte verlassen. Grund: " . $wrapper->getError();
                    $ship->leaveFleet();
                } else {
                    $fleetShip->enterStarSystem($systemId, $posx, $posy);
                    if ($fleetShip->isTraktorbeamActive()) {
                        $this->enterStarSystemTraktor($fleetShip, $game);
                    }
                }
                $fleetShip->save();
            }
            $game->addInformation("Die Flotte fliegt in das " . $system->getName() . "-System ein");
            $game->addInformationMerge($msg);
        } else {
            if ($ship->isInFleet()) {
                $ship->leaveFleet();
                $game->addInformation("Das Schiff hat die Flotte verlassen");
            }
            $game->addInformation("Das Schiff fliegt in das " . $system->getName() . "-System ein");
        }
        $ship->save();
    }

    private function enterStarSystemTraktor(ShipData $ship, GameControllerInterface $game): void
    {
        if ($ship->getEps() < 1) {
            $ship->getTraktorShip()->unsetTraktor();
            $ship->getTraktorShip()->save();
            $ship->unsetTraktor();
            $game->addInformation("Der Traktorstrahl auf die " . $ship->getTraktorShip()->getName() . " wurde beim Systemeinflug aufgrund Energiemangels deaktiviert");
            return;
        }
        $ship->getTraktorShip()->enterStarSystem($ship->getSystem()->getId(), $ship->getPosX(), $ship->getPosY());
        // @todo Beschädigung bei Systemeinflug
        $ship->lowerEps(1);
        $ship->getTraktorShip()->save();
        $ship->save();
        $game->addInformation("Die " . $ship->getTraktorShip()->getName() . " wurde mit in das System gezogen");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
