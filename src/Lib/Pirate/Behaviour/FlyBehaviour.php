<?php

namespace Stu\Lib\Pirate\Behaviour;

use Stu\Lib\Pirate\Component\Coordinate;
use Stu\Module\Control\StuRandom;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Lib\Pirate\Component\PirateFlightInterface;
use Stu\Lib\Pirate\Component\SafeFlightRouteInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;

class FlyBehaviour implements PirateBehaviourInterface
{
    private LoggerUtilInterface $logger;

    public function __construct(
        private FlightRouteFactoryInterface $flightRouteFactory,
        private SafeFlightRouteInterface $safeFlightRoute,
        private PirateFlightInterface $pirateFlight,
        private StuRandom $stuRandom,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->logger = $loggerUtilFactory->getLoggerUtil();
    }

    public function action(FleetWrapperInterface $fleet): void
    {
        $leadWrapper = $fleet->getLeadWrapper();
        $leadShip = $leadWrapper->get();

        $currentLocation = $leadShip->getCurrentMapField();

        if (
            $currentLocation instanceof StarSystemMapInterface
            && $this->stuRandom->rand(1, 4) === 1
        ) {
            $this->leaveStarSystem($leadWrapper, $currentLocation);

            $mapField = $currentLocation->getSystem()->getMapField();
            $this->logger->logf('    left star system: %s', $mapField !== null ? $mapField->getSectorString() : $currentLocation->getSectorString());
        }

        $currentLocation = $leadShip->getCurrentMapField();
        $this->logger->logf('    currentPosition: %s', $currentLocation->getSectorString());

        $flightRoute = $this->safeFlightRoute->getSafeFlightRoute(
            $leadShip,
            fn () => $this->getCoordinate($leadShip, $currentLocation)
        );
        if ($flightRoute === null) {
            $this->logger->log('    no safe flight route found');
            return;
        }

        $this->pirateFlight->movePirate($leadWrapper, $flightRoute);

        $newLocation = $leadShip->getCurrentMapField();
        $this->logger->logf('    newLocation: %s', $newLocation->getSectorString());
    }

    private function getCoordinate(
        ShipInterface $leadShip,
        MapInterface|StarSystemMapInterface $currentLocation
    ): Coordinate {
        $isInXDirection = $this->stuRandom->rand(0, 1) === 0;
        $maxFields = $leadShip->getSensorRange() * 2;

        return new Coordinate(
            $isInXDirection ? $currentLocation->getX() + $this->stuRandom->rand(-$maxFields, $maxFields) : $currentLocation->getX(),
            $isInXDirection ? $currentLocation->getY() : $currentLocation->getY() + $this->stuRandom->rand(-$maxFields, $maxFields)
        );
    }

    private function leaveStarSystem(ShipWrapperInterface $wrapper, StarSystemMapInterface $currentLocation): void
    {
        $mapField = $currentLocation->getSystem()->getMapField();
        if ($mapField === null) {
            return;
        }

        $flightRoute = $this->flightRouteFactory->getRouteForMapDestination(
            $mapField
        );

        $this->pirateFlight->movePirate($wrapper, $flightRoute);
    }
}
