<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteFactoryInterface;
use Stu\Module\Ship\Lib\Movement\Route\RandomSystemEntryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Lib\Pirate\Component\PirateFlightInterface;
use Stu\Module\Logging\PirateLoggerInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\StarSystemMapInterface;

class PirateNavigation implements PirateNavigationInterface
{
    private PirateLoggerInterface $logger;

    public function __construct(
        private MoveOnLayerInterface $moveOnLayer,
        private FlightRouteFactoryInterface $flightRouteFactory,
        private PirateFlightInterface $pirateFlight,
        private RandomSystemEntryInterface $randomSystemEntry,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->logger = $loggerUtilFactory->getPirateLogger();
    }

    public function navigateToTarget(
        FleetWrapperInterface $fleet,
        MapInterface|StarSystemMapInterface|StarSystemInterface $target
    ): bool {
        $leadWrapper = $fleet->getLeadWrapper();
        $leadShip = $leadWrapper->get();
        $shipSystem = $leadShip->getSystem();

        $targetSystem = $this->getTargetSystem($target);

        // leave system
        if (
            $shipSystem !== null
            && $shipSystem !== $targetSystem
        ) {
            $this->logger->logf('    leave current system "%s"', $shipSystem->getName());
            if (!$this->leaveStarSystem($leadWrapper, $shipSystem)) {
                $this->logger->log('    could not leave current system');
                return false;
            }
        }

        if ($targetSystem !== null) {
            if (!$this->navigateIntoSystem($leadWrapper, $targetSystem)) {
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

        // move to system
        if (
            $leadShip->getSystem() === null
            && $leadShip->isOverSystem() !== $system
        ) {
            $this->logger->log('    move to system');
            if (!$this->moveOnLayer->move($wrapper, $system->getMap())) {
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
        $mapField = $system->getMap();
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
