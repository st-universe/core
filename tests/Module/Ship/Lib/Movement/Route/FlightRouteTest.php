<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Route;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use RuntimeException;
use Stu\Config\Init;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Movement\Component\Consequence\FlightConsequenceInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\WormholeEntryInterface;
use Stu\StuTestCase;

use function DI\get;

/**
 * @runTestsInSeparateProcesses Avoid global settings to cause trouble within other tests
 */
class FlightRouteTest extends StuTestCase
{
    /** @var MockInterface&CheckDestinationInterface */
    private MockInterface $checkDestination;

    /** @var MockInterface&LoadWaypointsInterface */
    private MockInterface $loadWaypoints;

    /** @var MockInterface&EnterWaypointInterface */
    private MockInterface $enterWaypoint;

    private FlightConsequenceInterface $flightConsequence;

    private FlightRouteInterface $subject;

    protected function setUp(): void
    {
        $this->checkDestination = $this->mock(CheckDestinationInterface::class);
        $this->loadWaypoints = $this->mock(LoadWaypointsInterface::class);
        $this->enterWaypoint = $this->mock(EnterWaypointInterface::class);

        $this->flightConsequence = $this->mock(FlightConsequenceInterface::class);

        $this->subject = new FlightRoute(
            $this->checkDestination,
            $this->loadWaypoints,
            $this->enterWaypoint,
            [$this->flightConsequence],
            [$this->flightConsequence]
        );
    }

    public function testSetDestinationExpectOneMapWaypointWhenNotTranswarp(): void
    {
        $map = $this->mock(MapInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $messages = $this->mock(MessageCollectionInterface::class);

        $this->subject->setDestination($map, false);

        $this->assertSame($map, $this->subject->getNextWaypoint());

        $this->enterWaypoint->shouldReceive('enterNextWaypoint')
            ->with(
                $ship,
                false,
                $map,
                null
            )
            ->once();

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);

        $this->flightConsequence->shouldReceive('trigger')
            ->with($wrapper, $this->subject, $messages)
            ->twice();

        $this->subject->enterNextWaypoint($wrapper, $messages);

        $this->subject->stepForward();

        $this->assertTrue($this->subject->isDestinationArrived());
        $this->assertEquals(RouteModeEnum::ROUTE_MODE_SYSTEM_EXIT, $this->subject->getRouteMode());
    }

    public function testSetDestinationExpectOneMapWaypointWhenTranswarp(): void
    {
        $map = $this->mock(MapInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $messages = $this->mock(MessageCollectionInterface::class);

        $this->subject->setDestination($map, true);

        $this->assertSame($map, $this->subject->getNextWaypoint());

        $this->enterWaypoint->shouldReceive('enterNextWaypoint')
            ->with(
                $ship,
                false,
                $map,
                null
            )
            ->once();

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);

        $this->flightConsequence->shouldReceive('trigger')
            ->with($wrapper, $this->subject, $messages)
            ->twice();

        $this->subject->enterNextWaypoint($wrapper, $messages);

        $this->subject->stepForward();

        $this->assertTrue($this->subject->isDestinationArrived());
        $this->assertEquals(RouteModeEnum::ROUTE_MODE_TRANSWARP, $this->subject->getRouteMode());
    }

    public function testSetDestinationExpectOneSystemMapWaypoint(): void
    {
        $map = $this->mock(StarSystemMapInterface::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $messages = $this->mock(MessageCollectionInterface::class);

        $this->subject->setDestination($map, false);

        $this->assertSame($map, $this->subject->getNextWaypoint());

        $this->enterWaypoint->shouldReceive('enterNextWaypoint')
            ->with(
                $ship,
                false,
                $map,
                null
            )
            ->once();

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);

        $this->flightConsequence->shouldReceive('trigger')
            ->with($wrapper, $this->subject, $messages)
            ->twice();

        $this->subject->enterNextWaypoint($wrapper, $messages);

        $this->subject->stepForward();

        $this->assertTrue($this->subject->isDestinationArrived());
        $this->assertEquals(RouteModeEnum::ROUTE_MODE_SYSTEM_ENTRY, $this->subject->getRouteMode());
    }

    public function testSetDestinationViaWormholeExpectSystemMapAsWaypointWhenEntry(): void
    {
        $wormholeEntry = $this->mock(WormholeEntryInterface::class);
        $systemMap = $this->mock(StarSystemMapInterface::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $messages = $this->mock(MessageCollectionInterface::class);

        $wormholeEntry->shouldReceive('getSystemMap')
            ->withNoArgs()
            ->once()
            ->andReturn($systemMap);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);

        $this->subject->setDestinationViaWormhole($wormholeEntry, true);

        $this->enterWaypoint->shouldReceive('enterNextWaypoint')
            ->with(
                $ship,
                false,
                $systemMap,
                $wormholeEntry
            )
            ->once();

        $this->flightConsequence->shouldReceive('trigger')
            ->with($wrapper, $this->subject, $messages)
            ->twice();

        $this->subject->enterNextWaypoint($wrapper, $messages);

        $this->subject->stepForward();

        $this->assertTrue($this->subject->isDestinationArrived());
        $this->assertEquals(RouteModeEnum::ROUTE_MODE_WORMHOLE_ENTRY, $this->subject->getRouteMode());
    }

    public function testSetDestinationViaWormholeExpectSystemMapAsWaypointWhenExit(): void
    {
        $wormholeEntry = $this->mock(WormholeEntryInterface::class);
        $map = $this->mock(MapInterface::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $messages = $this->mock(MessageCollectionInterface::class);

        $wormholeEntry->shouldReceive('getMap')
            ->withNoArgs()
            ->once()
            ->andReturn($map);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);

        $this->subject->setDestinationViaWormhole($wormholeEntry, false);

        $this->enterWaypoint->shouldReceive('enterNextWaypoint')
            ->with(
                $ship,
                false,
                $map,
                $wormholeEntry
            )
            ->once();

        $this->flightConsequence->shouldReceive('trigger')
            ->with($wrapper, $this->subject, $messages)
            ->twice();

        $this->subject->enterNextWaypoint($wrapper, $messages);

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
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $messages = $this->mock(MessageCollectionInterface::class);
        $waypoints = new ArrayCollection();

        $waypoints->add($first);
        $waypoints->add($destination);

        $ship->shouldReceive('getCurrentMapField')
            ->withNoArgs()
            ->andReturn($start);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);

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
            ->with($ship, true, $first, null)
            ->once();

        $this->flightConsequence->shouldReceive('trigger')
            ->with($wrapper, $this->subject, $messages)
            ->twice();

        $this->subject->enterNextWaypoint($wrapper, $messages);

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

        $this->subject->setDestination($map, false);

        $this->assertSame($map, $this->subject->getNextWaypoint());

        $this->subject->abortFlight();

        $this->assertTrue($this->subject->isDestinationArrived());
    }

    public function testAllFlightConsequencesRegistered(): void
    {
        error_reporting(0);

        $output = 'some-output';

        static::expectOutputString($output);

        $container = null;
        $app = function ($c) use ($output, &$container): void {
            $container = $c;
            echo $output;
        };

        Init::run($app);

        $this->assertEquals(10, count(get('flight_consequences')->resolve($container)));
    }

    public function testAllPostFlightConsequencesRegistered(): void
    {
        error_reporting(0);

        $output = 'some-output';

        static::expectOutputString($output);

        $container = null;
        $app = function ($c) use ($output, &$container): void {
            $container = $c;
            echo $output;
        };

        Init::run($app);

        $this->assertEquals(5, count(get('post_flight_consequences')->resolve($container)));
    }
}
