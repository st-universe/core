<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\FleetActivateTorpedo;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class FleetActivateTorpedo implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_FLEET_ACTIVATE_TORPEDO';

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
        $msg[] = "Flottenbefehl ausgeführt: Aktivierung der Torpedobänke";
        foreach ($ship->getFleet()->getShips() as $key => $ship) {
            if (!$ship->hasTorpedo() || $ship->torpedoIsActive()) {
                continue;
            }
            if (!$ship->getTorpedoCount()) {
                $msg[] = $ship->getName() . _(": Keine Torpedos geladen");
                continue;
            }
            if ($ship->getEps() < SYSTEM_ECOST_TORPEDO) {
                $msg[] = $ship->getName() . _(": Nicht genügend Energie vorhanden");
                continue;
            }
            $ship->lowerEps(SYSTEM_ECOST_TORPEDO);
            $ship->setTorpedos(1);
            $ship->save();
        }
        $game->addInformationMerge($msg);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
