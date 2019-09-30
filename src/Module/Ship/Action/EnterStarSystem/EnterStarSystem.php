<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\EnterStarSystem;

use request;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class EnterStarSystem implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ENTER_STARSYSTEM';

    private $shipLoader;

    private $shipRepository;

    private $shipSystemManager;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository,
        ShipSystemManagerInterface $shipSystemManager
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
        $this->shipSystemManager = $shipSystemManager;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $system = $ship->isOverSystem();

        if (!$system) {
            return;
        }
        if ($ship->getBuildplan()->getCrew() > 0 && $ship->getCrewCount() === 0) {
            $game->addInformation(_('Das Schiff hat keine Crew'));
            return;
        }

        $flightDirection = $ship->getFlightDirection();
        if ($flightDirection === 0) {
            $flightDirection = rand(1,4);
        }

        switch ($flightDirection) {
            case 1:
                $posx = rand(1, $system->getMaxX());
                $posy = 1;
                break;
            case 2:
                $posx = rand(1, $system->getMaxX());
                $posy = $system->getMaxY();
                break;
            case 3:
                $posx = 1;
                $posy = rand(1, $system->getMaxY());
                break;
            case 4:
                $posx = $system->getMaxX();
                $posy = rand(1, $system->getMaxY());
                break;
        }

        $this->shipSystemManager->deactivate($ship, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);

        // @todo Beschädigung bei Systemeinflug
        $this->enterStarSystem($ship, $system, $posx, $posy);
        if ($ship->isTraktorbeamActive()) {
            $this->enterStarSystemTraktor($ship, $game);
        }

        if ($ship->isFleetLeader()) {
            $msg = [];

            /** @var ShipInterface[] $result */
            $result = array_filter(
                $ship->getFleet()->getShips()->toArray(),
                function (ShipInterface $fleetShip) use ($ship): bool {
                    return $ship !== $fleetShip;
                }
            );
            foreach ($result as $fleetShip) {
                if ($fleetShip->getBuildplan()->getCrew() > 0 && $fleetShip->getCrewCount() === 0) {
                    $msg[] = "Die " . $fleetShip->getName() . " hat die Flotte verlassen. Grund: Keine Crew";
                    $fleetShip->leaveFleet();
                    continue;
                }
                if ($fleetShip->getEps() === 0) {
                    $msg[] = "Die " . $fleetShip->getName() . " hat die Flotte verlassen. Grund: Energiemangel";
                    $fleetShip->leaveFleet();
                    continue;
                }

                $this->shipSystemManager->deactivate($fleetShip, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);

                $this->enterStarSystem($fleetShip, $system, $posx, $posy);
                if ($fleetShip->isTraktorbeamActive()) {
                    $this->enterStarSystemTraktor($fleetShip, $game);
                }

                $fleetShip->setEps($fleetShip->getEps() - 1);

                $this->shipRepository->save($fleetShip);
            }
            $game->addInformation("Die Flotte fliegt in das " . $system->getName() . "-System ein");
            $game->addInformationMerge($msg);
        } else {
            if ($ship->getFleetId()) {
                $ship->leaveFleet();
                $game->addInformation("Das Schiff hat die Flotte verlassen");
            }
            $game->addInformation("Das Schiff fliegt in das " . $system->getName() . "-System ein");
        }

        $this->shipRepository->save($ship);
    }

    private function enterStarSystemTraktor(ShipInterface $ship, GameControllerInterface $game): void
    {
        if ($ship->getEps() < 1) {
            $ship->getTraktorShip()->unsetTraktor();

            $this->shipRepository->save($ship->getTraktorShip());

            $ship->unsetTraktor();
            $game->addInformation("Der Traktorstrahl auf die " . $ship->getTraktorShip()->getName() . " wurde beim Systemeinflug aufgrund Energiemangels deaktiviert");
            return;
        }
        $this->enterStarSystem(
            $ship->getTraktorShip(),
            $ship->getSystem(),
            $ship->getPosX(),
            $ship->getPosY()
        );
        // @todo Beschädigung bei Systemeinflug
        $ship->setEps($ship->getEps() - 1);

        $this->shipRepository->save($ship->getTraktorShip());
        $this->shipRepository->save($ship);

        $game->addInformation("Die " . $ship->getTraktorShip()->getName() . " wurde mit in das System gezogen");
    }

    private function enterStarSystem(ShipInterface $ship, StarSystemInterface $starSystem, int $posx, int $posy): void
    {
        $ship->setSystem($starSystem);
        $ship->setSX($posx);
        $ship->setSY($posy);

        $this->shipRepository->save($ship);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
