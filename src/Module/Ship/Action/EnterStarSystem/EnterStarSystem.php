<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\EnterStarSystem;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use SystemActivationWrapper;

final class EnterStarSystem implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ENTER_STARSYSTEM';

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

        if (!$ship->isOverSystem()) {
            return;
        }
        $wrapper = new SystemActivationWrapper($ship);
        $wrapper->setVar('eps', 1);
        if ($wrapper->getError()) {
            $game->addInformation($wrapper->getError());
            return;
        }
        switch ($ship->getFlightDirection()) {
            case 1:
                $posx = rand(1, (int)$ship->getSystem()->getMaxX());
                $posy = 1;
                break;
            case 2:
                $posx = rand(1, (int)$ship->getSystem()->getMaxX());
                $posy = $ship->getSystem()->getMaxY();
                break;
            case 3:
                $posx = 1;
                $posy = rand(1, (int)$ship->getSystem()->getMaxY());
                break;
            case 4:
                $posx = $ship->getSystem()->getMaxX();
                $posy = rand(1, (int)$ship->getSystem()->getMaxY());
                break;
        }

        $system = $ship->getSystem();
        $systemId = $system->getId();

        // @todo Beschädigung bei Systemeinflug
        $this->enterStarSystem($ship, $systemId, $posx, $posy);
        if ($ship->isTraktorbeamActive()) {
            $this->enterStarSystemTraktor($ship, $game);
        }

        if ($ship->isFleetLeader()) {
            $msg = [];
            $result = array_filter(
                $ship->getFleet()->getShips()->toArray(),
                function (ShipInterface $fleetShip) use ($ship): bool {
                    return $ship->getId() !== $fleetShip;
                }
            );
            foreach ($result as $fleetShip) {
                /** @var ShipInterface $fleetShip */
                $wrapper = new SystemActivationWrapper($fleetShip);
                $wrapper->setVar('eps', 1);
                if ($wrapper->getError()) {
                    $msg[] = "Die " . $fleetShip->getName() . " hat die Flotte verlassen. Grund: " . $wrapper->getError();
                    $ship->leaveFleet();
                } else {
                    $this->enterStarSystem($fleetShip, $systemId, $posx, $posy);
                    if ($fleetShip->isTraktorbeamActive()) {
                        $this->enterStarSystemTraktor($fleetShip, $game);
                    }
                }

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
            $ship->getSystem()->getId(),
            $ship->getPosX(),
            $ship->getPosY()
        );
        // @todo Beschädigung bei Systemeinflug
        $ship->setEps($ship->getEps() - 1);

        $this->shipRepository->save($ship->getTraktorShip());
        $this->shipRepository->save($ship);

        $game->addInformation("Die " . $ship->getTraktorShip()->getName() . " wurde mit in das System gezogen");
    }

    private function enterStarSystem(ShipInterface $ship, int $systemId, int $posx, int $posy): void
    {
        $ship->setWarpState(false);
        $ship->setSystemsId($systemId);
        $ship->setSX($posx);
        $ship->setSY($posy);

        $this->shipRepository->save($ship);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
