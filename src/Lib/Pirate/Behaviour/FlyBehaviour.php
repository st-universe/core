<?php

namespace Stu\Lib\Pirate\Behaviour;

use Stu\Module\Control\StuRandom;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Lib\Pirate\Component\PirateFlightInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;

class FlyBehaviour implements PirateBehaviourInterface
{
    private FlightRouteFactoryInterface $flightRouteFactory;

    private PirateFlightInterface $pirateFlight;

    private StuRandom $stuRandom;

    private LoggerUtilInterface $logger;

    public function __construct(
        FlightRouteFactoryInterface $flightRouteFactory,
        PirateFlightInterface $pirateFlight,
        StuRandom $stuRandom,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->flightRouteFactory = $flightRouteFactory;
        $this->pirateFlight = $pirateFlight;
        $this->stuRandom = $stuRandom;

        $this->logger = $loggerUtilFactory->getLoggerUtil(true);
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
            $this->logger->log(sprintf('    left star system: %s', $mapField !== null ? $mapField->getSectorString() : $currentLocation->getSectorString()));
        }

        $currentLocation = $leadShip->getCurrentMapField();
        $this->logger->log(sprintf('    currentPosition: %s', $currentLocation->getSectorString()));

        $flightRoute = $this->getSafeFlightRoute($leadShip, $currentLocation);

        $this->pirateFlight->movePirate($leadWrapper, $flightRoute);

        $newLocation = $leadShip->getCurrentMapField();
        $this->logger->log(sprintf('    newLocation: %s', $newLocation->getSectorString()));
    }

    private function getSafeFlightRoute(
        ShipInterface $leadShip,
        MapInterface|StarSystemMapInterface $currentLocation
    ): FlightRouteInterface {
        $isInXDirection = $this->stuRandom->rand(0, 1) === 0;
        $maxFields = $leadShip->getSensorRange() * 2;

        do {
            $flightRoute = $this->flightRouteFactory->getRouteForCoordinateDestination(
                $leadShip,
                $isInXDirection ? $currentLocation->getX() + $this->stuRandom->rand(-$maxFields, $maxFields) : $currentLocation->getX(),
                $isInXDirection ? $currentLocation->getY() : $currentLocation->getY() + $this->stuRandom->rand(-$maxFields, $maxFields)
            );
        } while ($flightRoute->isRouteDangerous());

        return $flightRoute;
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
