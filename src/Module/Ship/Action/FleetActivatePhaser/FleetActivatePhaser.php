<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\FleetActivatePhaser;

use request;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class FleetActivatePhaser implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_FLEET_ACTIVATE_PHASER';

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
        $msg[] = "Flottenbefehl ausgeführt: Aktivierung der Strahlenwaffen";
        foreach ($ship->getFleet()->getShips() as $key => $ship) {
            if (!$ship->hasPhaser() || $ship->phaserIsActive()) {
                continue;
            }
            if ($ship->getEps() < SYSTEM_ECOST_PHASER) {
                $msg[] = $ship->getName() . ": Nicht genügend Energie vorhanden";
                continue;
            }
            $ship->lowerEps(SYSTEM_ECOST_PHASER);
            $ship->setPhaser(1);
            $ship->save();
        }
        $game->addInformationMerge($msg);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
