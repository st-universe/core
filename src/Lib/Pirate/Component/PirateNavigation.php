<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteFactoryInterface;
use Stu\Module\Ship\Lib\Movement\Route\RandomSystemEntryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Lib\Pirate\Component\PirateFlightInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\StarSystemMapInterface;

class PirateNavigation implements PirateNavigationInterface
{
    private MoveOnLayerInterface $moveOnLayer;

    private FlightRouteFactoryInterface $flightRouteFactory;

    private PirateFlightInterface $pirateFlight;

    private RandomSystemEntryInterface $randomSystemEntry;

    private LoggerUtilInterface $logger;

    public function __construct(
        MoveOnLayerInterface $moveOnLayer,
        FlightRouteFactoryInterface $flightRouteFactory,
        PirateFlightInterface $pirateFlight,
        RandomSystemEntryInterface $randomSystemEntry,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->moveOnLayer = $moveOnLayer;
        $this->flightRouteFactory = $flightRouteFactory;
        $this->pirateFlight = $pirateFlight;
        $this->randomSystemEntry = $randomSystemEntry;

        $this->logger = $loggerUtilFactory->getLoggerUtil(true);
    }

    public function navigateToTarget(
        FleetWrapperInterface $fleet,
        MapInterface|StarSystemMapInterface|StarSystemInterface $target
    ): bool {
        $leadWrapper = $fleet->getLeadWrapper();
        $leadShip = $leadWrapper->get();

        $system = $this->getTargetSystem($target);
        if ($system !== null) {
            if (!$this->navigateIntoSystem($leadWrapper, $system)) {
                return false;
            }
        }

        if ($target instanceof StarSystemInterface) {
            return true;
        }

        // move to target
        $currentLocation = $leadShip->getCurrentMapField();
        if ($currentLocation !== $target) {
            if (!$this->moveOnLayer->move($leadWrapper, $target)) {
                $this->logger->log('    did not reach target');
                return false;
            }
        }

        return true;
    }

    private function getTargetSystem(MapInterface|StarSystemMapInterface|StarSystemInterface $target): ?StarSystemInterface
    {
        if ($target instanceof StarSystemInterface) {
            return $target;
        }

        if ($target instanceof StarSystemMapInterface) {
            return $target->getSystem();
        }

        return null;
    }

    private function navigateIntoSystem(ShipWrapperInterface $wrapper, StarSystemInterface $system): bool
    {
        $leadShip = $wrapper->get();
        $shipSystem = $leadShip->getSystem();

        // leave system
        if (
            $shipSystem !== null
            && $shipSystem !== $system
        ) {
            $this->logger->logf('    leave current system "%s"', $shipSystem->getName());
            if (!$this->leaveStarSystem($wrapper, $shipSystem)) {
                $this->logger->log('    could not leave current system');
                return false;
            }
        }

        // move to system
        if (
            $leadShip->getSystem() === null
            && $leadShip->isOverSystem() !== $system
        ) {
            $this->logger->log('    move to system');
            if (!$this->moveOnLayer->move($wrapper, $system->getMapField())) {
                $this->logger->log('    could not reach system');
                return false;
            }
        }

        // enter system
        if ($leadShip->isOverSystem() === $system) {
            $this->logger->logf('    enter system "%s"', $system->getName());
            if (!$this->enterSystem($wrapper, $system)) {
                $this->logger->log('    could not enter system');
                return false;
            }
        }

        return true;
    }

    private function leaveStarSystem(ShipWrapperInterface $wrapper, StarSystemInterface $system): bool
    {
        $mapField = $system->getMapField();
        if ($mapField === null) {
            return false;
        }

        $flightRoute = $this->flightRouteFactory->getRouteForMapDestination(
            $mapField
        );

        $this->pirateFlight->movePirate($wrapper, $flightRoute);

        return $wrapper->get()->getSystem() === null;
    }

    private function enterSystem(ShipWrapperInterface $wrapper, StarSystemInterface $system): bool
    {
        $destination = $this->randomSystemEntry->getRandomEntryPoint($wrapper->get(), $system);

        $flightRoute = $this->flightRouteFactory->getRouteForMapDestination(
            $destination
        );

        $this->pirateFlight->movePirate($wrapper, $flightRoute);

        return $wrapper->get()->getSystem() === $system;
    }
}
