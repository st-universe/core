<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\FleetDeactivateWarp;

use request;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class FleetDeactivateWarp implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_FLEET_DEACTIVATE_WARP';

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

        foreach ($ship->getFleet()->getShips() as $key => $ship) {
            if (!$ship->getWarpState()) {
                continue;
            }
            if ($ship->traktorBeamFromShip()) {
                $ship->getTraktorShip()->setWarpState(1);
                $ship->getTraktorShip()->save();
            }
            $ship->setWarpState(0);
            $ship->save();
        }
        $game->addInformation(_('Flottenbefehl ausgeführt: Deaktivierung des Warpantriebs'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
