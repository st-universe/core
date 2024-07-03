<?php

declare(strict_types=1);

namespace Stu\Lib\Pirate\Component;

use Mockery\MockInterface;
use Override;
use Stu\Lib\Pirate\PirateCreation;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteFactoryInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\StuTestCase;

class SafeFlightRouteTest extends StuTestCase
{
    /** @var MockInterface|FlightRouteFactoryInterface */
    private $flightRouteFactory;

    /** @var MockInterface|ShipInterface */
    private $ship;

    private SafeFlightRouteInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->flightRouteFactory = $this->mock(FlightRouteFactoryInterface::class);

        $this->ship = $this->mock(ShipInterface::class);

        $this->subject = new SafeFlightRoute($this->flightRouteFactory);
    }

    public function testGetSafeFlightRouteExpectNullWhenTriesExceeded(): void
    {
        $flightRoute = $this->mock(FlightRouteInterface::class);
        $coordinate = $this->mock(Coordinate::class);

        $callable = fn () => $coordinate;

        $flightRoute->shouldReceive('isRouteDangerous')
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

        $flightRoute->shouldReceive('isRouteDangerous')
            ->withNoArgs()
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
