<?php

namespace Stu\Lib\Pirate\Behaviour;

use Stu\Lib\Pirate\Component\Coordinate;
use Stu\Lib\Pirate\Component\PirateFlightInterface;
use Stu\Lib\Pirate\Component\SafeFlightRouteInterface;
use Stu\Lib\Pirate\PirateBehaviourEnum;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionMetadata;
use Stu\Module\Control\StuRandom;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\PirateLoggerInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\StarSystemMap;

class FlyBehaviour implements PirateBehaviourInterface
{
    private PirateLoggerInterface $logger;

    public function __construct(
        private FlightRouteFactoryInterface $flightRouteFactory,
        private SafeFlightRouteInterface $safeFlightRoute,
        private PirateFlightInterface $pirateFlight,
        private StuRandom $stuRandom,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->logger = $loggerUtilFactory->getPirateLogger();
    }

    #[\Override]
    public function action(
        FleetWrapperInterface $fleet,
        PirateReactionInterface $pirateReaction,
        PirateReactionMetadata $reactionMetadata,
        ?Spacecraft $triggerSpacecraft
    ): ?PirateBehaviourEnum {
        $leadWrapper = $fleet->getLeadWrapper();
        $leadShip = $leadWrapper->get();

        $currentLocation = $leadShip->getLocation();

        if (
            $currentLocation instanceof StarSystemMap
            && $this->stuRandom->rand(1, 4) === 1
        ) {
            $this->leaveStarSystem($leadWrapper, $currentLocation);

            $mapField = $currentLocation->getSystem()->getMap();
            $this->logger->logf('    left star system: %s', $mapField !== null ? $mapField->getSectorString() : $currentLocation->getSectorString());
        }

        $currentLocation = $leadShip->getLocation();
        $this->logger->logf('    currentPosition: %s', $currentLocation->getSectorString());

        $flightRoute = $this->safeFlightRoute->getSafeFlightRoute(
            $leadShip,
            fn(): Coordinate => $this->getCoordinate($leadWrapper, $currentLocation)
        );
        if ($flightRoute === null) {
            $this->logger->log('    no safe flight route found');
            return null;
        }

        $this->pirateFlight->movePirate($leadWrapper, $flightRoute);

        $newLocation = $leadShip->getLocation();
        $this->logger->logf('    newLocation: %s', $newLocation->getSectorString());

        return null;
    }

    private function getCoordinate(
        SpacecraftWrapperInterface $leadWrapper,
        Location $currentLocation
    ): Coordinate {

        $isInXDirection = $this->stuRandom->rand(0, 1) === 0;
        $sensorRange = $leadWrapper->getLssSystemData()?->getSensorRange() ?? 0;
        $maxFields = $sensorRange * 2;

        return new Coordinate(
            $isInXDirection ? $currentLocation->getX() + $this->stuRandom->rand(-$maxFields, $maxFields) : $currentLocation->getX(),
            $isInXDirection ? $currentLocation->getY() : $currentLocation->getY() + $this->stuRandom->rand(-$maxFields, $maxFields)
        );
    }

    private function leaveStarSystem(ShipWrapperInterface $wrapper, StarSystemMap $currentLocation): void
    {
        $mapField = $currentLocation->getSystem()->getMap();
        if ($mapField === null) {
            return;
        }

        $flightRoute = $this->flightRouteFactory->getRouteForMapDestination(
            $mapField
        );

        $this->pirateFlight->movePirate($wrapper, $flightRoute);
    }
}
