<?php

declare(strict_types=1);

namespace Stu\Lib\Pirate\Component;

use Mockery\MockInterface;
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Lib\Pirate\PirateCreation;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteFactoryInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Orm\Entity\Ship;
use Stu\StuTestCase;

class SafeFlightRouteTest extends StuTestCase
{
    private MockInterface&FlightRouteFactoryInterface $flightRouteFactory;

    private MockInterface&Ship $ship;

    private SafeFlightRouteInterface $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->flightRouteFactory = $this->mock(FlightRouteFactoryInterface::class);

        $this->ship = $this->mock(Ship::class);

        $this->subject = new SafeFlightRoute($this->flightRouteFactory);
    }

    public function testGetSafeFlightRouteExpectNullWhenTriesExceeded(): void
    {
        $flightRoute = $this->mock(FlightRouteInterface::class);
        $coordinate = $this->mock(Coordinate::class);

        $callable = fn () => $coordinate;

        $flightRoute->shouldReceive('hasSpecialDamageOnField')
            ->withNoArgs()
            ->times(SafeFlightRoute::MAX_TRIES)
            ->andReturn(true);

        $coordinate->shouldReceive('getX')
            ->withNoArgs()
            ->times(SafeFlightRoute::MAX_TRIES)
            ->andReturn(5);
        $coordinate->shouldReceive('getY')
            ->withNoArgs()
            ->times(SafeFlightRoute::MAX_TRIES)
            ->andReturn(55);

        $this->flightRouteFactory->shouldReceive('getRouteForCoordinateDestination')
            ->with(
                $this->ship,
                5,
                55
            )
            ->times(SafeFlightRoute::MAX_TRIES)
            ->andReturn($flightRoute);

        $this->subject->getSafeFlightRoute(
            $this->ship,
            $callable
        );
    }

    public function testGetSafeFlightRouteExpectRouteWhenSafe(): void
    {
        $flightRoute = $this->mock(FlightRouteInterface::class);
        $coordinate = $this->mock(Coordinate::class);

        $callable = fn () => $coordinate;

        $flightRoute->shouldReceive('hasSpecialDamageOnField')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $flightRoute->shouldReceive('hasEffectOnRoute')
            ->with(FieldTypeEffectEnum::NO_PIRATES)
            ->once()
            ->andReturn(false);
        $flightRoute->shouldReceive('isDestinationInAdminRegion')
            ->with(PirateCreation::FORBIDDEN_ADMIN_AREAS)
            ->once()
            ->andReturn(false);
        $flightRoute->shouldReceive('isDestinationAtTradepost')
            ->with()
            ->once()
            ->andReturn(false);

        $coordinate->shouldReceive('getX')
            ->withNoArgs()
            ->once()
            ->andReturn(5);
        $coordinate->shouldReceive('getY')
            ->withNoArgs()
            ->once()
            ->andReturn(55);

        $this->flightRouteFactory->shouldReceive('getRouteForCoordinateDestination')
            ->with(
                $this->ship,
                5,
                55
            )
            ->once()
            ->andReturn($flightRoute);

        $result = $this->subject->getSafeFlightRoute(
            $this->ship,
            $callable
        );

        $this->assertSame($flightRoute, $result);
    }
}
