<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\PostFlight;

use Mockery\MockInterface;
use Stu\Component\Map\DirectionEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\FlightConsequenceInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\FlightSignatureCreatorInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\UpdateFlightDirectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Ship;
use Stu\StuTestCase;

class PostFlightDirectionConsequenceTest extends StuTestCase
{
    private MockInterface&FlightSignatureCreatorInterface $flightSignatureCreator;

    private MockInterface&UpdateFlightDirectionInterface $updateFlightDirection;

    private FlightConsequenceInterface $subject;

    private MockInterface&Ship $ship;

    private MockInterface&ShipWrapperInterface $wrapper;

    private MockInterface&FlightRouteInterface $flightRoute;

    #[\Override]
    protected function setUp(): void
    {
        $this->flightSignatureCreator = $this->mock(FlightSignatureCreatorInterface::class);
        $this->updateFlightDirection = $this->mock(UpdateFlightDirectionInterface::class);

        $this->ship = $this->mock(Ship::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->flightRoute = $this->mock(FlightRouteInterface::class);

        $this->wrapper->shouldReceive('get')
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->subject = new PostFlightDirectionConsequence(
            $this->flightSignatureCreator,
            $this->updateFlightDirection
        );
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

    public function testTriggerExpectNothingWhenNotTraversing(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);

        $this->ship->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->flightRoute->shouldReceive('isTraversing')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }

    public function testTriggerExpectDirectionUpdateOnlyWhenTractored(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);
        $oldWaypoint = $this->mock(Map::class);
        $newWaypoint = $this->mock(Map::class);

        $this->ship->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->flightRoute->shouldReceive('isTraversing')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->flightRoute->shouldReceive('getCurrentWaypoint')
            ->withNoArgs()
            ->once()
            ->andReturn($oldWaypoint);
        $this->flightRoute->shouldReceive('getNextWaypoint')
            ->withNoArgs()
            ->once()
            ->andReturn($newWaypoint);

        $this->updateFlightDirection->shouldReceive('updateWhenTraversing')
            ->with($oldWaypoint, $newWaypoint, $this->wrapper)
            ->once()
            ->andReturn(DirectionEnum::TOP);

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }

    public function testTriggerExpectFlightSignaturesWhenNotTractored(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);
        $oldWaypoint = $this->mock(Map::class);
        $newWaypoint = $this->mock(Map::class);

        $this->ship->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->flightRoute->shouldReceive('isTraversing')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->flightRoute->shouldReceive('getCurrentWaypoint')
            ->withNoArgs()
            ->once()
            ->andReturn($oldWaypoint);
        $this->flightRoute->shouldReceive('getNextWaypoint')
            ->withNoArgs()
            ->once()
            ->andReturn($newWaypoint);

        $this->updateFlightDirection->shouldReceive('updateWhenTraversing')
            ->with($oldWaypoint, $newWaypoint, $this->wrapper)
            ->once()
            ->andReturn(DirectionEnum::TOP);

        $this->flightSignatureCreator->shouldReceive('createSignatures')
            ->with(
                $this->ship,
                DirectionEnum::TOP,
                $oldWaypoint,
                $newWaypoint
            )
            ->once();

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }
}
