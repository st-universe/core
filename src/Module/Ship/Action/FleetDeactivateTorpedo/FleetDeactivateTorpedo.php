<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\FleetDeactivateTorpedo;

use request;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class FleetDeactivateTorpedo implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_FLEET_DEACTIVATE_TORPEDO';

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
            $ship->setTorpedos(0);
            $ship->save();
        }
        $game->addInformation(_("Flottenbefehl ausgeführt: Deaktivierung der Torpedobänke"));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
