<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Route;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use RuntimeException;
use Stu\Component\Map\Effects\EffectHandlingInterface;
use Stu\Config\Init;
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\Flight\FlightStartConsequenceInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\FlightConsequenceInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\PostFlight\PostFlightConsequenceInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Movement\FlightCompany;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\StationInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Entity\WormholeEntryInterface;
use Stu\StuTestCase;

use function DI\get;

class FlightRouteTest extends StuTestCase
{
    /** @var MockInterface&CheckDestinationInterface */
    private $checkDestination;
    /** @var MockInterface&LoadWaypointsInterface */
    private $loadWaypoints;
    /** @var MockInterface&EnterWaypointInterface */
    private $enterWaypoint;
    /** @var MockInterface&EffectHandlingInterface */
    private $effectHandling;

    /** @var MockInterface&FlightConsequenceInterface */
    private $flightConsequence;
    /** @var MockInterface&FlightConsequenceInterface */
    private $postFlightConsequence;

    private FlightRouteInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->checkDestination = $this->mock(CheckDestinationInterface::class);
        $this->loadWaypoints = $this->mock(LoadWaypointsInterface::class);
        $this->enterWaypoint = $this->mock(EnterWaypointInterface::class);
        $this->effectHandling = $this->mock(EffectHandlingInterface::class);

        $this->flightConsequence = $this->mock(FlightConsequenceInterface::class);
        $this->postFlightConsequence = $this->mock(FlightConsequenceInterface::class);

        $this->subject = new FlightRoute(
            $this->checkDestination,
            $this->loadWaypoints,
            $this->enterWaypoint,
            $this->effectHandling,
            [$this->flightConsequence],
            [$this->postFlightConsequence]
        );
    }

    public function testSetDestinationExpectOneMapWaypointWhenNotTranswarp(): void
    {
        $flightCompany = $this->mock(FlightCompany::class);
        $map = $this->mock(MapInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $tractoredShip = $this->mock(ShipInterface::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $tractoredShipWrapper = $this->mock(ShipWrapperInterface::class);
        $messages = $this->mock(MessageCollectionInterface::class);

        $this->subject->setDestination($map, false);

        $this->assertSame($map, $this->subject->getNextWaypoint());

        $flightCompany->shouldReceive('getActiveMembers')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$wrapper]));

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
        $wrapper->shouldReceive('getTractoredShipWrapper')
            ->withNoArgs()
            ->andReturn($tractoredShipWrapper);

        $tractoredShipWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($tractoredShip);
        $tractoredShipWrapper->shouldReceive('getTractoredShipWrapper')
            ->withNoArgs()
            ->andReturnNull();

        $this->flightConsequence->shouldReceive('trigger')
            ->with($wrapper, $this->subject, $messages)
            ->once();
        $this->postFlightConsequence->shouldReceive('trigger')
            ->with($wrapper, $this->subject, $messages)
            ->once();
        $this->flightConsequence->shouldReceive('trigger')
            ->with($tractoredShipWrapper, $this->subject, $messages)
            ->once();
        $this->postFlightConsequence->shouldReceive('trigger')
            ->with($tractoredShipWrapper, $this->subject, $messages)
            ->once();

        $this->effectHandling->shouldReceive('addFlightInformationForActiveEffects')
            ->with($map, $messages)
            ->once();

        $this->subject->enterNextWaypoint($flightCompany, $messages);

        $this->assertTrue($this->subject->isDestinationArrived());
        $this->assertEquals(RouteModeEnum::SYSTEM_EXIT, $this->subject->getRouteMode());
    }

    public function testSetDestinationExpectOneMapWaypointWhenTranswarp(): void
    {
        $flightCompany = $this->mock(FlightCompany::class);
        $map = $this->mock(MapInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $messages = $this->mock(MessageCollectionInterface::class);

        $this->subject->setDestination($map, true);

        $this->assertSame($map, $this->subject->getNextWaypoint());

        $flightCompany->shouldReceive('getActiveMembers')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$wrapper]));

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
        $wrapper->shouldReceive('getTractoredShipWrapper')
            ->withNoArgs()
            ->andReturnNull();

        $this->flightConsequence->shouldReceive('trigger')
            ->with($wrapper, $this->subject, $messages)
            ->once();
        $this->postFlightConsequence->shouldReceive('trigger')
            ->with($wrapper, $this->subject, $messages)
            ->once();

        $this->effectHandling->shouldReceive('addFlightInformationForActiveEffects')
            ->with($map, $messages)
            ->once();

        $this->subject->enterNextWaypoint($flightCompany, $messages);

        $this->assertTrue($this->subject->isDestinationArrived());
        $this->assertEquals(RouteModeEnum::TRANSWARP, $this->subject->getRouteMode());
    }

    public function testSetDestinationExpectOneSystemMapWaypoint(): void
    {
        $flightCompany = $this->mock(FlightCompany::class);
        $map = $this->mock(StarSystemMapInterface::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $messages = $this->mock(MessageCollectionInterface::class);

        $this->subject->setDestination($map, false);

        $this->assertSame($map, $this->subject->getNextWaypoint());

        $flightCompany->shouldReceive('getActiveMembers')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$wrapper]));

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
        $wrapper->shouldReceive('getTractoredShipWrapper')
            ->withNoArgs()
            ->andReturnNull();

        $this->flightConsequence->shouldReceive('trigger')
            ->with($wrapper, $this->subject, $messages)
            ->once();
        $this->postFlightConsequence->shouldReceive('trigger')
            ->with($wrapper, $this->subject, $messages)
            ->once();

        $this->effectHandling->shouldReceive('addFlightInformationForActiveEffects')
            ->with($map, $messages)
            ->once();

        $this->subject->enterNextWaypoint($flightCompany, $messages);

        $this->assertTrue($this->subject->isDestinationArrived());
        $this->assertEquals(RouteModeEnum::SYSTEM_ENTRY, $this->subject->getRouteMode());
    }

    public function testSetDestinationViaWormholeExpectSystemMapAsWaypointWhenEntry(): void
    {
        $flightCompany = $this->mock(FlightCompany::class);
        $wormholeEntry = $this->mock(WormholeEntryInterface::class);
        $systemMap = $this->mock(StarSystemMapInterface::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $messages = $this->mock(MessageCollectionInterface::class);

        $flightCompany->shouldReceive('getActiveMembers')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$wrapper]));

        $wormholeEntry->shouldReceive('getSystemMap')
            ->withNoArgs()
            ->once()
            ->andReturn($systemMap);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);
        $wrapper->shouldReceive('getTractoredShipWrapper')
            ->withNoArgs()
            ->andReturnNull();

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
            ->once();
        $this->postFlightConsequence->shouldReceive('trigger')
            ->with($wrapper, $this->subject, $messages)
            ->once();

        $this->effectHandling->shouldReceive('addFlightInformationForActiveEffects')
            ->with($systemMap, $messages)
            ->once();

        $this->subject->enterNextWaypoint($flightCompany, $messages);

        $this->assertTrue($this->subject->isDestinationArrived());
        $this->assertEquals(RouteModeEnum::WORMHOLE_ENTRY, $this->subject->getRouteMode());
    }

    public function testSetDestinationViaWormholeExpectSystemMapAsWaypointWhenExit(): void
    {
        $flightCompany = $this->mock(FlightCompany::class);
        $wormholeEntry = $this->mock(WormholeEntryInterface::class);
        $map = $this->mock(MapInterface::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $messages = $this->mock(MessageCollectionInterface::class);

        $flightCompany->shouldReceive('getActiveMembers')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$wrapper]));

        $wormholeEntry->shouldReceive('getMap')
            ->withNoArgs()
            ->once()
            ->andReturn($map);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);
        $wrapper->shouldReceive('getTractoredShipWrapper')
            ->withNoArgs()
            ->andReturnNull();


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
            ->once();
        $this->postFlightConsequence->shouldReceive('trigger')
            ->with($wrapper, $this->subject, $messages)
            ->once();

        $this->effectHandling->shouldReceive('addFlightInformationForActiveEffects')
            ->with($map, $messages)
            ->once();

        $this->subject->enterNextWaypoint($flightCompany, $messages);

        $this->assertTrue($this->subject->isDestinationArrived());
        $this->assertEquals(RouteModeEnum::WORMHOLE_EXIT, $this->subject->getRouteMode());
    }

    public function testSetDestinationViaCoordinatesExpectValidationOnlyWhenStartEqualsDestination(): void
    {
        $start = $this->mock(MapInterface::class);
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getLocation')
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
        $flightCompany = $this->mock(FlightCompany::class);
        $start = $this->mock(MapInterface::class);
        $first = $this->mock(MapInterface::class);
        $destination = $this->mock(MapInterface::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $messages = $this->mock(MessageCollectionInterface::class);
        $waypoints = new ArrayCollection();

        $waypoints->add($first);
        $waypoints->add($destination);

        $flightCompany->shouldReceive('getActiveMembers')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$wrapper]));

        $ship->shouldReceive('getLocation')
            ->withNoArgs()
            ->andReturn($start);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);
        $wrapper->shouldReceive('getTractoredShipWrapper')
            ->withNoArgs()
            ->andReturnNull();

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
            ->once();
        $this->postFlightConsequence->shouldReceive('trigger')
            ->with($wrapper, $this->subject, $messages)
            ->once();

        $this->effectHandling->shouldReceive('addFlightInformationForActiveEffects')
            ->with($first, $messages)
            ->once();

        $this->subject->enterNextWaypoint($flightCompany, $messages);
        $this->assertEquals($destination, $this->subject->getNextWaypoint());
        $this->assertFalse($this->subject->isDestinationArrived());
        $this->assertEquals(RouteModeEnum::FLIGHT, $this->subject->getRouteMode());
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
        $dic = Init::getContainer();

        $this->assertEquals(13, count(get(FlightStartConsequenceInterface::class)->resolve($dic)));
    }

    public function testAllPostFlightConsequencesRegistered(): void
    {
        $dic = Init::getContainer();

        $this->assertEquals(8, count(get(PostFlightConsequenceInterface::class)->resolve($dic)));
    }

    public function testHasSpecialDamageOnFieldExpectFalseIfWaypointsWithoutSpecialDamage(): void
    {
        $start = $this->mock(MapInterface::class);
        $destination = $this->mock(MapInterface::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $waypoints = new ArrayCollection();

        $waypoints->add($destination);

        $ship->shouldReceive('getLocation')
            ->withNoArgs()
            ->andReturn($start);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);

        $destination->shouldReceive('getFieldType->getSpecialDamage')
            ->withNoArgs()
            ->once()
            ->andReturn(0);

        $this->checkDestination->shouldReceive('validate')
            ->with($ship, 42, 5)
            ->once()
            ->andReturn($destination);

        $this->loadWaypoints->shouldReceive('load')
            ->with($start, $destination)
            ->once()
            ->andReturn($waypoints);

        $this->subject->setDestinationViaCoordinates($ship, 42, 5);

        $result = $this->subject->hasSpecialDamageOnField();

        $this->assertFalse($result);
    }

    public function testHasSpecialDamageOnFieldExpectTrueIfWaypointWithSpecialDamage(): void
    {
        $start = $this->mock(MapInterface::class);
        $first = $this->mock(MapInterface::class);
        $destination = $this->mock(MapInterface::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $waypoints = new ArrayCollection();

        $waypoints->add($first);
        $waypoints->add($destination);

        $ship->shouldReceive('getLocation')
            ->withNoArgs()
            ->andReturn($start);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);

        $first->shouldReceive('getFieldType->getSpecialDamage')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $this->checkDestination->shouldReceive('validate')
            ->with($ship, 42, 5)
            ->once()
            ->andReturn($destination);

        $this->loadWaypoints->shouldReceive('load')
            ->with($start, $destination)
            ->once()
            ->andReturn($waypoints);

        $this->subject->setDestinationViaCoordinates($ship, 42, 5);

        $result = $this->subject->hasSpecialDamageOnField();

        $this->assertTrue($result);
    }

    public function testHasEffectOnRouteExpectFalseIfNoEffect(): void
    {
        $start = $this->mock(MapInterface::class);
        $destination = $this->mock(MapInterface::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $waypoints = new ArrayCollection();

        $waypoints->add($destination);

        $ship->shouldReceive('getLocation')
            ->withNoArgs()
            ->andReturn($start);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);

        $destination->shouldReceive('getFieldType->hasEffect')
            ->with(FieldTypeEffectEnum::NO_PIRATES)
            ->once()
            ->andReturn(false);

        $this->checkDestination->shouldReceive('validate')
            ->with($ship, 42, 5)
            ->once()
            ->andReturn($destination);

        $this->loadWaypoints->shouldReceive('load')
            ->with($start, $destination)
            ->once()
            ->andReturn($waypoints);

        $this->subject->setDestinationViaCoordinates($ship, 42, 5);

        $result = $this->subject->hasEffectOnRoute(FieldTypeEffectEnum::NO_PIRATES);

        $this->assertFalse($result);
    }

    public function testHasEffectOnRouteExpectTrueIfEffectExistent(): void
    {
        $start = $this->mock(MapInterface::class);
        $first = $this->mock(MapInterface::class);
        $destination = $this->mock(MapInterface::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $waypoints = new ArrayCollection();

        $waypoints->add($first);
        $waypoints->add($destination);

        $ship->shouldReceive('getLocation')
            ->withNoArgs()
            ->andReturn($start);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);

        $first->shouldReceive('getFieldType->hasEffect')
            ->with(FieldTypeEffectEnum::NO_PIRATES)
            ->once()
            ->andReturn(true);

        $this->checkDestination->shouldReceive('validate')
            ->with($ship, 42, 5)
            ->once()
            ->andReturn($destination);

        $this->loadWaypoints->shouldReceive('load')
            ->with($start, $destination)
            ->once()
            ->andReturn($waypoints);

        $this->subject->setDestinationViaCoordinates($ship, 42, 5);

        $result = $this->subject->hasEffectOnRoute(FieldTypeEffectEnum::NO_PIRATES);

        $this->assertTrue($result);
    }

    public function testIsDestinationInAdminRegionExpectTrueIfMatch(): void
    {
        $start = $this->mock(MapInterface::class);
        $destination = $this->mock(MapInterface::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $waypoints = new ArrayCollection();

        $waypoints->add($destination);

        $ship->shouldReceive('getLocation')
            ->withNoArgs()
            ->andReturn($start);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);

        $destination->shouldReceive('getAdminRegionId')
            ->withNoArgs()
            ->once()
            ->andReturn(778899);

        $this->checkDestination->shouldReceive('validate')
            ->with($ship, 42, 5)
            ->once()
            ->andReturn($destination);

        $this->loadWaypoints->shouldReceive('load')
            ->with($start, $destination)
            ->once()
            ->andReturn($waypoints);

        $this->subject->setDestinationViaCoordinates($ship, 42, 5);

        $result = $this->subject->isDestinationInAdminRegion([778899]);

        $this->assertTrue($result);
    }

    public function testIsDestinationInAdminRegionExpectFalseIfNoMatch(): void
    {
        $start = $this->mock(MapInterface::class);
        $destination = $this->mock(MapInterface::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $waypoints = new ArrayCollection();

        $waypoints->add($destination);

        $ship->shouldReceive('getLocation')
            ->withNoArgs()
            ->andReturn($start);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);

        $destination->shouldReceive('getAdminRegionId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->checkDestination->shouldReceive('validate')
            ->with($ship, 42, 5)
            ->once()
            ->andReturn($destination);

        $this->loadWaypoints->shouldReceive('load')
            ->with($start, $destination)
            ->once()
            ->andReturn($waypoints);

        $this->subject->setDestinationViaCoordinates($ship, 42, 5);

        $result = $this->subject->isDestinationInAdminRegion([41, 43]);

        $this->assertFalse($result);
    }

    public function testIsDestinationAtTradepostExpectFalseWhenNotOnMap(): void
    {
        $destination = $this->mock(StarSystemMapInterface::class);

        $this->subject->setDestination($destination, false);

        $result = $this->subject->isDestinationAtTradepost();

        $this->assertFalse($result);
    }

    public function testIsDestinationAtTradepostExpectFalseWhenNoShipsOnMap(): void
    {
        $destination = $this->mock(MapInterface::class);

        $shiplist = new ArrayCollection();

        $destination->shouldReceive('getSpacecrafts')
            ->withNoArgs()
            ->once()
            ->andReturn($shiplist);

        $this->subject->setDestination($destination, false);

        $result = $this->subject->isDestinationAtTradepost();

        $this->assertFalse($result);
    }

    public function testIsDestinationAtTradepostExpectFalseWhenNoTradepost(): void
    {
        $destination = $this->mock(MapInterface::class);
        $station = $this->mock(StationInterface::class);

        $spacecraftList = new ArrayCollection([$station]);

        $destination->shouldReceive('getSpacecrafts')
            ->withNoArgs()
            ->once()
            ->andReturn($spacecraftList);

        $station->shouldReceive('getTradePost')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->subject->setDestination($destination, false);

        $result = $this->subject->isDestinationAtTradepost();

        $this->assertFalse($result);
    }

    public function testIsDestinationAtTradepostExpectTrueWhenTradepostOnMap(): void
    {
        $destination = $this->mock(MapInterface::class);
        $station = $this->mock(StationInterface::class);

        $spacecraftList = new ArrayCollection([$station]);

        $destination->shouldReceive('getSpacecrafts')
            ->withNoArgs()
            ->once()
            ->andReturn($spacecraftList);

        $station->shouldReceive('getTradePost')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(TradePostInterface::class));

        $this->subject->setDestination($destination, false);

        $result = $this->subject->isDestinationAtTradepost();

        $this->assertTrue($result);
    }
}
