<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\Flight;

use Mockery\MockInterface;
use RuntimeException;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\FlightConsequenceInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\UpdateFlightDirectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\RouteModeEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\StarSystemMap;
use Stu\StuTestCase;

class FlightDirectionConsequenceTest extends StuTestCase
{
    private MockInterface&UpdateFlightDirectionInterface $updateFlightDirection;

    private FlightConsequenceInterface $subject;

    private MockInterface&Ship $ship;

    private MockInterface&ShipWrapperInterface $wrapper;

    private MockInterface&FlightRouteInterface $flightRoute;

    #[\Override]
    protected function setUp(): void
    {
        $this->updateFlightDirection = $this->mock(UpdateFlightDirectionInterface::class);

        $this->ship = $this->mock(Ship::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->flightRoute = $this->mock(FlightRouteInterface::class);

        $this->wrapper->shouldReceive('get')
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->subject = new FlightDirectionConsequence($this->updateFlightDirection);
    }

    public function testTriggerExpectNothingWhenShipDestroyed(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);

        $this->ship->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }

    public function testTriggerExpectNothingWhenNotLeavingSystem(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);

        $this->ship->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->flightRoute->shouldReceive('getRouteMode')
            ->withNoArgs()
            ->once()
            ->andReturn(RouteModeEnum::FLIGHT);

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }

    public function testTriggerExpectExceptionWhenWrongWaypoint(): void
    {
        static::expectExceptionMessage('this should not happen');
        static::expectException(RuntimeException::class);

        $messages = $this->mock(MessageCollectionInterface::class);
        $oldWaypoint = $this->mock(Map::class);

        $this->ship->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getLocation')
            ->withNoArgs()
            ->once()
            ->andReturn($oldWaypoint);

        $this->flightRoute->shouldReceive('getRouteMode')
            ->withNoArgs()
            ->once()
            ->andReturn(RouteModeEnum::SYSTEM_EXIT);

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }

    public function testTriggerExpectUpdateWhenLeavingSystem(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);
        $oldWaypoint = $this->mock(StarSystemMap::class);

        $this->ship->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getLocation')
            ->withNoArgs()
            ->once()
            ->andReturn($oldWaypoint);

        $this->flightRoute->shouldReceive('getRouteMode')
            ->withNoArgs()
            ->once()
            ->andReturn(RouteModeEnum::SYSTEM_EXIT);

        $this->updateFlightDirection->shouldReceive('updateWhenSystemExit')
            ->with($this->wrapper, $oldWaypoint)
            ->once();

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }
}
