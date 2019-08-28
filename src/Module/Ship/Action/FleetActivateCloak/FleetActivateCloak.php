<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\FleetActivateCloak;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class FleetActivateCloak implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_FLEET_ACTIVATE_CLOAK';

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
        $msg[] = "Flottenbefehl ausgeführt: Aktivierung der Tarnung";
        foreach ($ship->getFleet()->getShips() as $key => $ship) {
            if (!$ship->isCloakable()) {
                continue;
            }
            if ($ship->getEps() < SYSTEM_ECOST_CLOAK) {
                $msg[] = $ship->getName() . _(": Nicht genügend Energie vorhanden");
                continue;
            }
            if ($ship->shieldIsActive()) {
                $ship->setShieldState(0);
                $msg[] = $ship->getName() . _(": Schilde deaktiviert");
            }
            if ($ship->isDocked()) {
                $ship->setDock(0);
                $msg[] = $ship->getName() . _(": Abgedockt");
            }
            $ship->lowerEps(SYSTEM_ECOST_CLOAK);
            $ship->setCloak(1);
            $ship->save();
        }
        $game->addInformationMerge($msg);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
