<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\EnterStarSystem;

use request;
use RuntimeException;
use Stu\Component\Ship\ShipEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\Exception\AlreadyOffException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\Utility\TractorMassPayloadUtilInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemInterface;
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

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository,
        ShipSystemManagerInterface $shipSystemManager,
        StarSystemMapRepositoryInterface $starSystemMapRepository,
        TractorMassPayloadUtilInterface $tractorMassPayloadUtil,
        ShipWrapperFactoryInterface $shipWrapperFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->starSystemMapRepository = $starSystemMapRepository;
        $this->tractorMassPayloadUtil = $tractorMassPayloadUtil;
        $this->shipWrapperFactory = $shipWrapperFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $ship = $wrapper->get();

        $system = $ship->isOverSystem();

        if ($system === null) {
            return;
        }

        if (!$this->doShipChecks($ship, $game)) {
            return;
        }

        [$posx, $posy] = $this->getDestinationCoordinates($ship, $system);

        // the destination starsystem map field
        $starsystemMap = $this->starSystemMapRepository->getByCoordinates($system->getId(), $posx, $posy);

        if ($starsystemMap === null) {
            throw new RuntimeException('starsystem map is missing');
        }

        try {
            $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
        } catch (AlreadyOffException $e) {
            //system was already offline
        }

        // @todo Beschädigung bei Systemeinflug
        $this->enterStarSystem($ship, $starsystemMap);
        if ($ship->isTractoring()) {
            $this->enterStarSystemTraktor($wrapper, $starsystemMap, $game);
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
                $wrapper = $this->shipWrapperFactory->wrapShip($fleetShip);

                if (!$fleetShip->hasEnoughCrew()) {
                    $msg[] = sprintf(
                        _("Die %s hat die Flotte verlassen. Grund: Zu wenig Crew"),
                        $fleetShip->getName()
                    );
                    $wrapper->leaveFleet();
                    continue;
                }

                $epsSystem = $wrapper->getEpsSystemData();

                if ($epsSystem->getEps() === 0) {
                    $msg[] = "Die " . $fleetShip->getName() . " hat die Flotte verlassen. Grund: Energiemangel";
                    $wrapper->leaveFleet();
                    continue;
                }

                try {
                    $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
                } catch (AlreadyOffException $e) {
                    //system was already offline
                }

                $this->enterStarSystem($fleetShip, $starsystemMap);
                if ($fleetShip->isTractoring()) {
                    $this->enterStarSystemTraktor($wrapper, $starsystemMap, $game);
                }

                $epsSystem->setEps($epsSystem->getEps() - 1)->update();

                $this->shipRepository->save($fleetShip);
            }
            $game->addInformation("Die Flotte fliegt in das " . $system->getName() . "-System ein");
            $game->addInformationMerge($msg);
        } else {
            if ($ship->getFleetId()) {
                $wrapper->leaveFleet();
                $game->addInformation("Das Schiff hat die Flotte verlassen");
            }
            $game->addInformation("Das Schiff fliegt in das " . $system->getName() . "-System ein");
        }

        //TODO alert red?

        $this->shipRepository->save($ship);
    }

    private function doShipChecks(ShipInterface $ship, GameControllerInterface $game): bool
    {
        if (!$ship->hasEnoughCrew($game)) {
            return false;
        }

        if ($ship->isTractored()) {
            $game->addInformation(_('Das Schiff wird von einem Traktorstrahl gehalten'));
            return false;
        }

        if ($ship->getHoldingWeb() !== null && $ship->getHoldingWeb()->isFinished()) {
            $game->addInformation(_('Das Schiff ist in einem Energienetz gefangen'));
            return false;
        }

        return true;
    }

    /**
     * @return array{0: int,1: int}
     */
    private function getDestinationCoordinates(ShipInterface $ship, StarSystemInterface $system): array
    {
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
            default:
                throw new RuntimeException('unsupported flight direction');
        }

        return [$posx, $posy];
    }

    private function enterStarSystemTraktor(ShipWrapperInterface $wrapper, StarSystemMapInterface $starsystemMap, GameControllerInterface $game): void
    {
        $ship = $wrapper->get();

        $tractoredShip = $ship->getTractoredShip();

        if ($tractoredShip === null) {
            return;
        }

        if (
            $tractoredShip->getFleet() !== null
            && $tractoredShip->getFleet()->getShipCount() > 1
        ) {
            $name = $tractoredShip->getName();
            $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true); //active deactivation

            $game->addInformation(sprintf(
                _('Flottenschiffe können nicht mitgezogen werden - Der auf die %s gerichtete Traktorstrahl wurde beim Systemeinflug deaktiviert'),
                $name
            ));
            return;
        }

        $abortionMsg = $this->tractorMassPayloadUtil->tryToTow($wrapper, $tractoredShip);
        if ($abortionMsg !== null) {
            $game->addInformation($abortionMsg);
            return;
        }

        $epsSystem = $wrapper->getEpsSystemData();

        if ($epsSystem->getEps() < 1) {
            $name = $tractoredShip->getName();
            $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true); //active deactivation
            $game->addInformation("Der Traktorstrahl auf die " . $name . " wurde beim Systemeinflug aufgrund Energiemangels deaktiviert");
            return;
        }
        $this->enterStarSystem(
            $tractoredShip,
            $starsystemMap
        );
        // @todo Beschädigung bei Systemeinflug
        $epsSystem->setEps($epsSystem->getEps() - 1)->update();
        $game->addInformation("Die " . $tractoredShip->getName() . " wurde mit in das System gezogen");

        //check for tractor system health
        $msg = [];
        $this->tractorMassPayloadUtil->tractorSystemSurvivedTowing($wrapper, $tractoredShip, $msg);
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
