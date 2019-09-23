<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\FleetActivateNbs;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class FleetActivateNbs implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_FLEET_ACTIVATE_NBS';

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
        $msg[] = "Flottenbefehl ausgeführt: Aktivierung der Nahbereichssensoren";
        foreach ($ship->getFleet()->getShips() as $key => $ship) {
            if ($ship->getNbs()) {
                continue;
            }
            if ($ship->getEps() < SYSTEM_ECOST_NBS) {
                $msg[] = $ship->getName() . ": Nicht genügend Energie vorhanden";
                continue;
            }
            $ship->setNbs(true);

            $ship->setEps($ship->getEps() - SYSTEM_ECOST_NBS);

            $this->shipRepository->save($ship);
        }
        $game->addInformationMerge($msg);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
