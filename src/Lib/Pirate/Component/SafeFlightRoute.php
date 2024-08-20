<?php

namespace Stu\Lib\Pirate\Component;

use Override;
use Stu\Lib\Pirate\PirateCreation;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteFactoryInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Orm\Entity\ShipInterface;

class SafeFlightRoute implements SafeFlightRouteInterface
{
    public const MAX_TRIES = 10;

    public function __construct(
        private FlightRouteFactoryInterface $flightRouteFactory
    ) {}

    #[Override]
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
        } while (
            $flightRoute->hasSpecialDamageOnField()
            || $flightRoute->isDestinationInAdminRegion(PirateCreation::FORBIDDEN_ADMIN_AREAS)
            || $flightRoute->isDestinationAtTradepost()
        );

        return $flightRoute;
    }
}
