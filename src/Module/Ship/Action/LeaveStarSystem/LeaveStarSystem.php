<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\LeaveStarSystem;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use SystemActivationWrapper;

final class LeaveStarSystem implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_LEAVE_STARSYSTEM';

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

        if ($ship->getSystem() === null) {
            return;
        }
        $wrapper = new SystemActivationWrapper($ship);
        $wrapper->setVar('eps', 1);
        if ($wrapper->getError()) {
            $game->addInformation($wrapper->getError());
            return;
        }
        $ship->cancelRepair();
        $this->leaveStarSystem($ship);
        if ($ship->isTraktorbeamActive()) {
            $this->leaveStarSystemTraktor($ship, $game);
        }
        if ($ship->isFleetLeader()) {
            $msg = array();
            $result = array_filter(
                $ship->getFleet()->getShips()->toArray(),
                function (ShipInterface $fleetShip) use ($ship): bool {
                    return $ship->getId() !== $fleetShip;
                }
            );
            foreach ($result as $key => $fleetShip) {
                /** @var ShipInterface $fleetShip */
                $wrapper = new SystemActivationWrapper($fleetShip);
                $wrapper->setVar('eps', 1);
                if ($wrapper->getError()) {
                    $msg[] = "Die " . $ship->getName() . " hat die Flotte verlassen. Grund: " . $wrapper->getError();
                    $ship->leaveFleet();
                } else {
                    $this->leaveStarSystem($fleetShip);
                    if ($fleetShip->isTraktorbeamActive()) {
                        $this->leaveStarSystemTraktor($fleetShip, $game);
                    }
                }

                $this->shipRepository->save($fleetShip);
            }
            $game->addInformation("Die Flotte hat das Sternensystem verlassen");
            $game->addInformationMerge($msg);
        } else {
            if ($ship->getFleetId()) {
                $ship->leaveFleet();
                $game->addInformation("Das Schiff hat die Flotte verlassen");
            }
            $game->addInformation("Das Sternensystem wurde verlassen");
        }

        $this->shipRepository->save($ship);
    }

    private function leaveStarSystemTraktor(ShipInterface $ship, GameControllerInterface $game): void
    {
        if ($ship->getEps() < 1) {
            $ship->getTraktorShip()->unsetTraktor();

            $this->shipRepository->save($ship->getTraktorShip());

            $ship->unsetTraktor();
            $game->addInformation("Der Traktorstrahl auf die " . $ship->getTraktorShip()->getName() . " wurde beim Verlassen des Systems aufgrund Energiemangels deaktiviert");
            return;
        }
        $this->leaveStarSystem($ship->getTraktorShip());
        $ship->setEps($ship->getEps() - 1);

        $this->shipRepository->save($ship->getTraktorShip());
        $this->shipRepository->save($ship);

        $game->addInformation("Die " . $ship->getTraktorShip()->getName() . " wurde mit aus dem System gezogen");
    }

    private function leaveStarSystem(ShipInterface $ship): void {
        $ship->setWarpState(true);
        $ship->setSystem(null);
        $ship->setSX(0);
        $ship->setSY(0);
    }


    public function performSessionCheck(): bool
    {
        return true;
    }
}
