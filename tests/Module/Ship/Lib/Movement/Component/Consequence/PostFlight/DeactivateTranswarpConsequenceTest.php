<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\Consequence\PostFlight;

use Override;
use Mockery\MockInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Movement\Component\Consequence\FlightConsequenceInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\Movement\Route\RouteModeEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\StuTestCase;

class DeactivateTranswarpConsequenceTest extends StuTestCase
{
    /** @var MockInterface&ShipSystemManagerInterface */
    private MockInterface $shipSystemManager;

    private FlightConsequenceInterface $subject;

    /** @var MockInterface&ShipInterface */
    private MockInterface $ship;

    /** @var MockInterface&ShipWrapperInterface */
    private MockInterface $wrapper;

    /** @var MockInterface&FlightRouteInterface */
    private MockInterface $flightRoute;

    #[Override]
    protected function setUp(): void
    {
        $this->shipSystemManager = $this->mock(ShipSystemManagerInterface::class);

        $this->ship = $this->mock(ShipInterface::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->flightRoute = $this->mock(FlightRouteInterface::class);

        $this->wrapper->shouldReceive('get')
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->subject = new DeactivateTranswarpConsequence(
            $this->shipSystemManager
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

    public function testTriggerExpectNothingWhenTractored(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);

        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }

    public function testTriggerExpectNothingWhenNotTranswarping(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);

        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->flightRoute->shouldReceive('getRouteMode')
            ->withNoArgs()
            ->once()
            ->andReturn(RouteModeEnum::ROUTE_MODE_FLIGHT);

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }

    public function testTriggerExpectDeactivationWhenTranswarping(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);

        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->flightRoute->shouldReceive('getRouteMode')
            ->withNoArgs()
            ->once()
            ->andReturn(RouteModeEnum::ROUTE_MODE_TRANSWARP);

        $this->shipSystemManager->shouldReceive('deactivate')
            ->with($this->wrapper, ShipSystemTypeEnum::SYSTEM_TRANSWARP_COIL, true)
            ->once();

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }
}
