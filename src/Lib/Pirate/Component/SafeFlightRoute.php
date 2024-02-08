<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Module\Ship\Lib\Movement\Route\FlightRouteFactoryInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Orm\Entity\ShipInterface;

class SafeFlightRoute implements SafeFlightRouteInterface
{
    public const MAX_TRIES = 10;

    private FlightRouteFactoryInterface $flightRouteFactory;

    public function __construct(
        FlightRouteFactoryInterface $flightRouteFactory
    ) {
        $this->flightRouteFactory = $flightRouteFactory;
    }

    public function getSafeFlightRoute(
        ShipInterface $ship,
        callable $coordinateCallable
    ): ?FlightRouteInterface {

        $triesLeft = self::MAX_TRIES;

        do {
            if ($triesLeft-- === 0) {
                return null;
            }

            /** @var Coordinate */
            $coordinate = $coordinateCallable();

            $flightRoute = $this->flightRouteFactory->getRouteForCoordinateDestination(
                $ship,
                $coordinate->getX(),
                $coordinate->getY()
            );
        } while ($flightRoute->isRouteDangerous());

        return $flightRoute;
    }
}
