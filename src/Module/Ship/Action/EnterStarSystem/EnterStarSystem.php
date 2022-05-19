<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\EnterStarSystem;

use request;
use Stu\Component\Ship\ShipEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\Exception\AlreadyOffException;
use Stu\Component\Ship\System\Utility\TractorMassPayloadUtilInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

final class EnterStarSystem implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ENTER_STARSYSTEM';

    private ShipLoaderInterface $shipLoader;

    private ShipRepositoryInterface $shipRepository;

    private ShipSystemManagerInterface $shipSystemManager;

    private StarSystemMapRepositoryInterface $starSystemMapRepository;

    private TractorMassPayloadUtilInterface $tractorMassPayloadUtil;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository,
        ShipSystemManagerInterface $shipSystemManager,
        StarSystemMapRepositoryInterface $starSystemMapRepository,
        TractorMassPayloadUtilInterface $tractorMassPayloadUtil
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->starSystemMapRepository = $starSystemMapRepository;
        $this->tractorMassPayloadUtil = $tractorMassPayloadUtil;
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
        if (!$ship->hasEnoughCrew($game)) {
            return;
        }

        if ($ship->isTractored()) {
            $game->addInformation(_('Das Schiff wird von einem Traktorstrahl gehalten'));
            return;
        }

        $flightDirection = $ship->getFlightDirection();
        if ($flightDirection === 0) {
            $flightDirection = rand(1, 4);
        }

        switch ($flightDirection) {
            case ShipEnum::DIRECTION_BOTTOM:
                $posx = rand(1, $system->getMaxX());
                $posy = 1;
                break;
            case ShipEnum::DIRECTION_TOP:
                $posx = rand(1, $system->getMaxX());
                $posy = $system->getMaxY();
                break;
            case ShipEnum::DIRECTION_RIGHT:
                $posx = 1;
                $posy = rand(1, $system->getMaxY());
                break;
            case ShipEnum::DIRECTION_LEFT:
                $posx = $system->getMaxX();
                $posy = rand(1, $system->getMaxY());
                break;
        }

        // the destination starsystem map field
        $starsystemMap = $this->starSystemMapRepository->getByCoordinates($system->getId(), $posx, $posy);

        try {
            $this->shipSystemManager->deactivate($ship, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
        } catch (AlreadyOffException $e) {
        }

        // @todo Beschädigung bei Systemeinflug
        $this->enterStarSystem($ship, $starsystemMap);
        if ($ship->isTractoring()) {
            $this->enterStarSystemTraktor($ship, $starsystemMap, $game);
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
                if (!$fleetShip->hasEnoughCrew()) {
                    $msg[] = sprintf(
                        _("Die %s hat die Flotte verlassen. Grund: Zu wenig Crew"),
                        $fleetShip->getName()
                    );
                    $fleetShip->leaveFleet();
                    continue;
                }
                if ($fleetShip->getEps() === 0) {
                    $msg[] = "Die " . $fleetShip->getName() . " hat die Flotte verlassen. Grund: Energiemangel";
                    $fleetShip->leaveFleet();
                    continue;
                }

                try {
                    $this->shipSystemManager->deactivate($fleetShip, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
                } catch (AlreadyOffException $e) {
                }

                $this->enterStarSystem($fleetShip, $starsystemMap);
                if ($fleetShip->isTractoring()) {
                    $this->enterStarSystemTraktor($fleetShip, $starsystemMap, $game);
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

        //TODO alert red?

        $this->shipRepository->save($ship);
    }

    private function enterStarSystemTraktor(ShipInterface $ship, StarSystemMapInterface $starsystemMap, GameControllerInterface $game): void
    {
        $tractoredShip = $ship->getTractoredShip();

        if (
            $tractoredShip->getFleetId()
            && $tractoredShip->getFleet()->getShipCount() > 1
        ) {
            $name = $tractoredShip->getName();
            $ship->deactivateTractorBeam(); //active deactivation

            $game->addInformation(sprintf(
                _('Flottenschiffe können nicht mitgezogen werden - Der auf die %s gerichtete Traktorstrahl wurde beim Systemeinflug deaktiviert'),
                $name
            ));
            return;
        }

        $abortionMsg = $this->tractorMassPayloadUtil->tryToTow($ship, $tractoredShip);
        if ($abortionMsg !== null) {
            $game->addInformation($abortionMsg);
            return;
        }

        if ($ship->getEps() < 1) {
            $name = $tractoredShip->getName();
            $ship->deactivateTractorBeam(); //active deactivation
            $game->addInformation("Der Traktorstrahl auf die " . $name . " wurde beim Systemeinflug aufgrund Energiemangels deaktiviert");
            return;
        }
        $this->enterStarSystem(
            $tractoredShip,
            $starsystemMap
        );
        // @todo Beschädigung bei Systemeinflug
        $ship->setEps($ship->getEps() - 1);
        $game->addInformation("Die " . $tractoredShip->getName() . " wurde mit in das System gezogen");

        //check for tractor system health
        $msg = [];
        $this->tractorMassPayloadUtil->tractorSystemSurvivedTowing($ship, $tractoredShip, $msg);
        $game->addInformationMergeDown($msg);


        $this->shipRepository->save($tractoredShip);
        $this->shipRepository->save($ship);
    }

    private function enterStarSystem(ShipInterface $ship, StarSystemMapInterface $starsystemMap): void
    {
        $ship->updateLocation(null, $starsystemMap);
        $ship->setDockedTo(null);
        $ship->setState(ShipStateEnum::SHIP_STATE_NONE);

        $this->shipRepository->save($ship);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
