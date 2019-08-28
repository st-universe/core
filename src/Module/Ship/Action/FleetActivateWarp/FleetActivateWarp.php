<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\FleetActivateWarp;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class FleetActivateWarp implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_FLEET_ACTIVATE_WARP';

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

        $msg = array();
        $msg[] = "Flottenbefehl ausgeführt: Aktivierung des Warpantriebs";
        foreach ($ship->getFleet()->getShips() as $key => $ship) {
            if (!$ship->isWarpable()) {
                continue;
            }
            // @todo warpantrieb beschaedigt
            if ($ship->isDocked()) {
                if ($ship->getEps() < SYSTEM_ECOST_DOCK) {
                    $msg[] = $ship->getName() . _(': Nicht genügend Energie zum Abdocken vorhanden');
                    continue;
                }
                $ship->setDock(0);
                $ship->lowerEps(SYSTEM_ECOST_DOCK);
                $ship->save();
            }
            if ($ship->getEps() < SYSTEM_ECOST_WARP) {
                $msg[] = $ship->getName() . _(": Nicht genügend Energie vorhanden");
                continue;
            }
            $ship->lowerEps(SYSTEM_ECOST_WARP);
            if ($ship->traktorBeamFromShip()) {
                if ($ship->getEps() < SYSTEM_ECOST_TRACTOR) {
                    $msg[] = $ship->getName() . _(": Traktorstrahl aufgrund von Energiemangel deaktiviert");
                    $ship->getTraktorShip()->unsetTraktor();
                    $ship->getTraktorShip()->save();
                    $ship->unsetTraktor();
                } else {
                    $ship->getTraktorShip()->setWarpState(1);
                    $ship->getTraktorShip()->save();
                    $ship->lowerEps(1);
                }
            }
            $ship->setWarpState(1);
            $ship->save();
        }
        $game->addInformationMerge($msg);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
