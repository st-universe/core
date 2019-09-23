<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\FleetActivateWarp;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class FleetActivateWarp implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_FLEET_ACTIVATE_WARP';

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
        $msg[] = "Flottenbefehl ausgeführt: Aktivierung des Warpantriebs";
        foreach ($ship->getFleet()->getShips() as $key => $ship) {
            if (!$ship->isWarpable()) {
                continue;
            }
            // @todo warpantrieb beschaedigt
            if ($ship->getDock()) {
                if ($ship->getEps() < SYSTEM_ECOST_DOCK) {
                    $msg[] = $ship->getName() . _(': Nicht genügend Energie zum Abdocken vorhanden');
                    continue;
                }
                $ship->setDock(0);
                $ship->setEps($ship->getEps() - SYSTEM_ECOST_DOCK);

                $this->shipRepository->save($ship);
            }
            if ($ship->getEps() < SYSTEM_ECOST_WARP) {
                $msg[] = $ship->getName() . _(": Nicht genügend Energie vorhanden");
                continue;
            }
            $ship->setEps($ship->getEps() - SYSTEM_ECOST_WARP);
            if ($ship->traktorBeamFromShip()) {
                if ($ship->getEps() < SYSTEM_ECOST_TRACTOR) {
                    $msg[] = $ship->getName() . _(": Traktorstrahl aufgrund von Energiemangel deaktiviert");
                    $ship->getTraktorShip()->unsetTraktor();

                    $this->shipRepository->save($ship->getTraktorShip());
                    $ship->unsetTraktor();
                } else {
                    $ship->getTraktorShip()->setWarpState(true);

                    $this->shipRepository->save($ship->getTraktorShip());
                    $ship->setEps($ship->getEps() - 1);
                }
            }
            $ship->setWarpState(true);

            $this->shipRepository->save($ship);
        }
        $game->addInformationMerge($msg);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
