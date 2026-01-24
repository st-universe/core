<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\PirateLoggerInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteFactoryInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\RandomSystemEntryInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\PirateFleetBattleParty;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\StarSystem;
use Stu\Orm\Entity\StarSystemMap;

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

    #[\Override]
    public function navigateToTarget(
        PirateFleetBattleParty $pirateFleetBattleParty,
        Location|StarSystem $target
    ): bool {
        $leadWrapper = $pirateFleetBattleParty->getLeader();
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

        if ($targetSystem !== null && !$this->navigateIntoSystem($leadWrapper, $targetSystem)) {
            return false;
        }

        if ($target instanceof StarSystem) {
            return true;
        }

        // move to target
        $currentLocation = $leadShip->getLocation();
        if ($currentLocation !== $target && !$this->moveOnLayer->move($leadWrapper, $target)) {
            $this->logger->log('    did not reach target');
            return false;
        }

        return true;
    }

    private function getTargetSystem(Location|StarSystem $target): ?StarSystem
    {
        if ($target instanceof StarSystem) {
            return $target;
        }

        if ($target instanceof StarSystemMap) {
            return $target->getSystem();
        }

        return null;
    }

    private function navigateIntoSystem(SpacecraftWrapperInterface $wrapper, StarSystem $system): bool
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

    private function leaveStarSystem(SpacecraftWrapperInterface $wrapper, StarSystem $system): bool
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

    private function enterSystem(SpacecraftWrapperInterface $wrapper, StarSystem $system): bool
    {
        $destination = $this->randomSystemEntry->getRandomEntryPoint($wrapper, $system);

        $flightRoute = $this->flightRouteFactory->getRouteForMapDestination(
            $destination
        );

        $this->pirateFlight->movePirate($wrapper, $flightRoute);

        return $wrapper->get()->getSystem() === $system;
    }
}
