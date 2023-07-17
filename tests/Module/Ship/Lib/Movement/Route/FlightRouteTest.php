<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Route;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use RuntimeException;
use Stu\Lib\InformationWrapper;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\WormholeEntryInterface;
use Stu\StuTestCase;

class FlightRouteTest extends StuTestCase
{
    /** @var MockInterface&CheckDestinationInterface */
    private MockInterface $checkDestination;

    /** @var MockInterface&LoadWaypointsInterface */
    private MockInterface $loadWaypoints;

    /** @var MockInterface&EnterWaypointInterface */
    private MockInterface $enterWaypoint;

    private FlightRouteInterface $subject;

    protected function setUp(): void
    {
        $this->checkDestination = $this->mock(CheckDestinationInterface::class);
        $this->loadWaypoints = $this->mock(LoadWaypointsInterface::class);
        $this->enterWaypoint = $this->mock(EnterWaypointInterface::class);

        $this->subject = new FlightRoute(
            $this->checkDestination,
            $this->loadWaypoints,
            $this->enterWaypoint
        );
    }

    public function testSetDestinationExpectOneMapWaypoint(): void
    {
        $map = $this->mock(MapInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $informationWrapper = $this->mock(InformationWrapper::class);

        $this->subject->setDestination($map);

        $this->assertSame($map, $this->subject->getNextWaypoint());

        $this->enterWaypoint->shouldReceive('enterNextWaypoint')
            ->with(
                $ship,
                false,
                $map,
                null,
                $informationWrapper
            )
            ->once();

        $this->subject->enterNextWaypoint($ship, $map, $informationWrapper);

        $this->subject->stepForward();

        $this->assertTrue($this->subject->isDestinationArrived());
        $this->assertEquals(RouteModeEnum::ROUTE_MODE_SYSTEM_EXIT, $this->subject->getRouteMode());
    }

    public function testSetDestinationExpectOneSystemMapWaypoint(): void
    {
        $map = $this->mock(StarSystemMapInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $informationWrapper = $this->mock(InformationWrapper::class);

        $this->subject->setDestination($map);

        $this->assertSame($map, $this->subject->getNextWaypoint());

        $this->enterWaypoint->shouldReceive('enterNextWaypoint')
            ->with(
                $ship,
                false,
                $map,
                null,
                $informationWrapper
            )
            ->once();

        $this->subject->enterNextWaypoint($ship, $map, $informationWrapper);

        $this->subject->stepForward();

        $this->assertTrue($this->subject->isDestinationArrived());
        $this->assertEquals(RouteModeEnum::ROUTE_MODE_SYSTEM_ENTRY, $this->subject->getRouteMode());
    }

    public function testSetDestinationViaWormholeExpectSystemMapAsWaypointWhenEntry(): void
    {
        $wormholeEntry = $this->mock(WormholeEntryInterface::class);
        $systemMap = $this->mock(StarSystemMapInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $informationWrapper = $this->mock(InformationWrapper::class);

        $wormholeEntry->shouldReceive('getSystemMap')
            ->withNoArgs()
            ->once()
            ->andReturn($systemMap);

        $this->subject->setDestinationViaWormhole($wormholeEntry, true);

        $this->enterWaypoint->shouldReceive('enterNextWaypoint')
            ->with(
                $ship,
                false,
                $systemMap,
                $wormholeEntry,
                $informationWrapper
            )
            ->once();

        $this->subject->enterNextWaypoint($ship, $systemMap, $informationWrapper);

        $this->subject->stepForward();

        $this->assertTrue($this->subject->isDestinationArrived());
        $this->assertEquals(RouteModeEnum::ROUTE_MODE_WORMHOLE_ENTRY, $this->subject->getRouteMode());
    }

    public function testSetDestinationViaWormholeExpectSystemMapAsWaypointWhenExit(): void
    {
        $wormholeEntry = $this->mock(WormholeEntryInterface::class);
        $map = $this->mock(MapInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $informationWrapper = $this->mock(InformationWrapper::class);

        $wormholeEntry->shouldReceive('getMap')
            ->withNoArgs()
            ->once()
            ->andReturn($map);

        $this->subject->setDestinationViaWormhole($wormholeEntry, false);

        $this->enterWaypoint->shouldReceive('enterNextWaypoint')
            ->with(
                $ship,
                false,
                $map,
                $wormholeEntry,
                $informationWrapper
            )
            ->once();

        $this->subject->enterNextWaypoint($ship, $map, $informationWrapper);

        $this->subject->stepForward();

        $this->assertTrue($this->subject->isDestinationArrived());
        $this->assertEquals(RouteModeEnum::ROUTE_MODE_WORMHOLE_EXIT, $this->subject->getRouteMode());
    }

    public function testSetDestinationViaCoordinatesExpectValidationOnlyWhenStartEqualsDestination(): void
    {
        $start = $this->mock(MapInterface::class);
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getCurrentMapField')
            ->withNoArgs()
            ->andReturn($start);

        $this->checkDestination->shouldReceive('validate')
            ->with($ship, 42, 5)
            ->once()
            ->andReturn($start);

        $this->subject->setDestinationViaCoordinates($ship, 42, 5);

        $this->assertTrue($this->subject->isDestinationArrived());
    }

    public function testSetDestinationViaCoordinatesExpectValidationAndWaypointLoading(): void
    {
        $start = $this->mock(MapInterface::class);
        $first = $this->mock(MapInterface::class);
        $destination = $this->mock(MapInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $informations = $this->mock(InformationWrapper::class);
        $waypoints = new ArrayCollection();

        $waypoints->add($first);
        $waypoints->add($destination);

        $ship->shouldReceive('getCurrentMapField')
            ->withNoArgs()
            ->andReturn($start);

        $this->checkDestination->shouldReceive('validate')
            ->with($ship, 42, 5)
            ->once()
            ->andReturn($destination);

        $this->loadWaypoints->shouldReceive('load')
            ->with($start, $destination)
            ->once()
            ->andReturn($waypoints);

        $this->subject->setDestinationViaCoordinates($ship, 42, 5);

        $this->assertEquals($first, $this->subject->getNextWaypoint());

        $this->enterWaypoint->shouldReceive('enterNextWaypoint')
            ->with($ship, true, $first, null, $informations)
            ->once();

        $this->subject->enterNextWaypoint($ship, $first, $informations);

        $this->subject->stepForward();

        $this->assertEquals($destination, $this->subject->getNextWaypoint());

        $this->subject->stepForward();

        $this->assertTrue($this->subject->isDestinationArrived());
        $this->assertEquals(RouteModeEnum::ROUTE_MODE_FLIGHT, $this->subject->getRouteMode());

        $this->subject->stepForward();
    }

    public function testGetNextWaypointExpectExceptionWhenWaypointsEmpty(): void
    {
        static::expectExceptionMessage('isDestinationArrived has to be called beforehand');
        static::expectException(RuntimeException::class);

        $this->subject->getNextWaypoint();
    }

    public function testAbortFlightExpectWaypointClearance(): void
    {
        $map = $this->mock(MapInterface::class);

        $this->subject->setDestination($map);

        $this->assertSame($map, $this->subject->getNextWaypoint());

        $this->subject->abortFlight();

        $this->assertTrue($this->subject->isDestinationArrived());
    }
}
