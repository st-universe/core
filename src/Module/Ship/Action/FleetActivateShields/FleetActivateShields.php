<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\FleetActivateShields;

use request;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class FleetActivateShields implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_FLEET_ACTIVATE_SHIELDS';

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
        $msg[] = "Flottenbefehl ausgeführt: Aktivierung der Schilde";
        foreach ($ship->getFleet()->getShips() as $key => $ship) {
            if ($ship->shieldIsActive()) {
                continue;
            }
            if ($ship->getShield() < 1) {
                $msg[] = $ship->getName() . _(": Die Schilde sind nicht aufgeladen");
                continue;
            }
            if ($ship->cloakIsActive()) {
                $msg[] = $ship->getName() . ": Die Tarnung ist aktiviert";
                continue;
            }
            if ($ship->getEps() < SYSTEM_ECOST_SHIELDS) {
                $msg[] = $ship->getName() . ": Nicht genügend Energie vorhanden";
                continue;
            }
            if ($ship->isDocked()) {
                $msg[] = $ship->getName() . _(": Abgedockt");
                $ship->setDock(0);
            }
            $ship->cancelRepair();
            $ship->lowerEps(SYSTEM_ECOST_SHIELDS);
            $ship->setShieldState(1);
            $ship->save();
        }
        $game->addInformationMerge($msg);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
