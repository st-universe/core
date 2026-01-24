<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Lib\Pirate\PirateCreation;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteFactoryInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;

class SafeFlightRoute implements SafeFlightRouteInterface
{
    public const MAX_TRIES = 10;

    public function __construct(
        private FlightRouteFactoryInterface $flightRouteFactory
    ) {}

    #[\Override]
    public function getSafeFlightRoute(
        Spacecraft $spacecraft,
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
                $spacecraft,
                $coordinate->getX(),
                $coordinate->getY()
            );
        } while (
            $flightRoute->hasSpecialDamageOnField()
            || $flightRoute->hasEffectOnRoute(FieldTypeEffectEnum::NO_PIRATES)
            || $flightRoute->isDestinationInAdminRegion(PirateCreation::FORBIDDEN_ADMIN_AREAS)
            || $flightRoute->isDestinationAtTradepost()
        );

        return $flightRoute;
    }
}
