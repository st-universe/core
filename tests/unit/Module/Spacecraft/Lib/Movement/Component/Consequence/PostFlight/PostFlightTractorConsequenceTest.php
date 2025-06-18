<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\PostFlight;

use Mockery\MockInterface;
use Override;
use Stu\Component\Spacecraft\System\Utility\TractorMassPayloadUtilInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\FlightConsequenceInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Station\Lib\StationWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StationInterface;
use Stu\StuTestCase;

class PostFlightTractorConsequenceTest extends StuTestCase
{
    /** @var MockInterface&TractorMassPayloadUtilInterface */
    private MockInterface $tractorMassPayloadUtil;

    private FlightConsequenceInterface $subject;

    /** @var MockInterface&StationInterface */
    private MockInterface $ship;

    /** @var MockInterface&StationWrapperInterface */
    private MockInterface $wrapper;

    /** @var MockInterface&FlightRouteInterface */
    private MockInterface $flightRoute;

    #[Override]
    protected function setUp(): void
    {
        $this->tractorMassPayloadUtil = $this->mock(TractorMassPayloadUtilInterface::class);

        $this->ship = $this->mock(StationInterface::class);
        $this->wrapper = $this->mock(StationWrapperInterface::class);
        $this->flightRoute = $this->mock(FlightRouteInterface::class);

        $this->wrapper->shouldReceive('get')
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->subject = new PostFlightTractorConsequence(
            $this->tractorMassPayloadUtil
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

    public function testTriggerExpectNothingWhenNotTractoring(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);

        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getTractoredShip')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }

    public function testTriggerExpectTractorSystemStress(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);
        $tractoredShip = $this->mock(ShipInterface::class);

        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getTractoredShip')
            ->withNoArgs()
            ->once()
            ->andReturn($tractoredShip);

        $this->tractorMassPayloadUtil->shouldReceive('stressTractorSystemForTowing')
            ->with($this->wrapper, $tractoredShip, $messages)
            ->once();

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }
}
