<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\Consequence\PostFlight;

use Mockery\MockInterface;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Movement\Component\Consequence\FlightConsequenceInterface;
use Stu\Module\Ship\Lib\Movement\Component\FlightSignatureCreatorInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\Movement\Route\UpdateFlightDirectionInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\StuTestCase;

class PostFlightDirectionConsequenceTest extends StuTestCase
{
    /** @var MockInterface&FlightSignatureCreatorInterface */
    private MockInterface $flightSignatureCreator;

    /** @var MockInterface&UpdateFlightDirectionInterface */
    private MockInterface $updateFlightDirection;

    private FlightConsequenceInterface $subject;

    /** @var MockInterface&ShipInterface */
    private MockInterface $ship;

    /** @var MockInterface&ShipWrapperInterface */
    private MockInterface $wrapper;

    /** @var MockInterface&FlightRouteInterface */
    private MockInterface $flightRoute;

    protected function setUp(): void
    {
        $this->flightSignatureCreator = $this->mock(FlightSignatureCreatorInterface::class);
        $this->updateFlightDirection = $this->mock(UpdateFlightDirectionInterface::class);

        $this->ship = $this->mock(ShipInterface::class);
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

        $this->ship->shouldReceive('isDestroyed')
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

        $this->ship->shouldReceive('isDestroyed')
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
        $oldWaypoint = $this->mock(MapInterface::class);
        $newWaypoint = $this->mock(MapInterface::class);

        $this->ship->shouldReceive('isDestroyed')
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
            ->with($oldWaypoint, $newWaypoint, $this->ship)
            ->once()
            ->andReturn(42);

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }

    public function testTriggerExpectFlightSignaturesWhenNotTractored(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);
        $oldWaypoint = $this->mock(MapInterface::class);
        $newWaypoint = $this->mock(MapInterface::class);

        $this->ship->shouldReceive('isDestroyed')
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
            ->with($oldWaypoint, $newWaypoint, $this->ship)
            ->once()
            ->andReturn(42);

        $this->flightSignatureCreator->shouldReceive('createSignatures')
            ->with(
                $this->ship,
                42,
                $oldWaypoint,
                $newWaypoint
            )
            ->once()
            ->andReturn(42);

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }
}
