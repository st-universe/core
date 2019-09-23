<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\FleetActivateShields;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class FleetActivateShields implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_FLEET_ACTIVATE_SHIELDS';

    private $shipLoader;

    private $shipRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
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
        foreach ($ship->getFleet()->getShips() as $ship) {
            if ($ship->getShieldState()) {
                continue;
            }
            if ($ship->getShield() < 1) {
                $msg[] = $ship->getName() . _(": Die Schilde sind nicht aufgeladen");
                continue;
            }
            if ($ship->getCloakState()) {
                $msg[] = $ship->getName() . ": Die Tarnung ist aktiviert";
                continue;
            }
            if ($ship->getEps() < SYSTEM_ECOST_SHIELDS) {
                $msg[] = $ship->getName() . ": Nicht genügend Energie vorhanden";
                continue;
            }
            if ($ship->getDock()) {
                $msg[] = $ship->getName() . _(": Abgedockt");
                $ship->setDock(0);
            }
            $ship->cancelRepair();
            $ship->setEps($ship->getEps() - SYSTEM_ECOST_SHIELDS);
            $ship->setShieldState(true);

            $this->shipRepository->save($ship);
        }
        $game->addInformationMerge($msg);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
