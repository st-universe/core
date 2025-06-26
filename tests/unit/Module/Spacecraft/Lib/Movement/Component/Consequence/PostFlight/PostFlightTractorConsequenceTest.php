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
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Station;
use Stu\StuTestCase;

class PostFlightTractorConsequenceTest extends StuTestCase
{
    private MockInterface&TractorMassPayloadUtilInterface $tractorMassPayloadUtil;

    private FlightConsequenceInterface $subject;

    private MockInterface&Station $ship;

    private MockInterface&StationWrapperInterface $wrapper;

    private MockInterface&FlightRouteInterface $flightRoute;

    #[Override]
    protected function setUp(): void
    {
        $this->tractorMassPayloadUtil = $this->mock(TractorMassPayloadUtilInterface::class);

        $this->ship = $this->mock(Station::class);
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

    public function testTriggerExpectNothingWhenNotTractoring(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);

        $this->ship->shouldReceive('getCondition->isDestroyed')
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
        $tractoredShip = $this->mock(Ship::class);

        $this->ship->shouldReceive('getCondition->isDestroyed')
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
