<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\Consequence\Flight;

use Mockery\MockInterface;
use Override;
use Stu\Component\Ship\System\Data\EpsSystemData;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Movement\Component\Consequence\FlightConsequenceInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\Movement\Route\RouteModeEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\StuTestCase;

class EpsConsequenceTest extends StuTestCase
{
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
        $this->ship = $this->mock(ShipInterface::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->flightRoute = $this->mock(FlightRouteInterface::class);

        $this->wrapper->shouldReceive('get')
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->subject = new EpsConsequence();
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

    public function testTriggerExpectNothingWhenShipTractored(): void
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

    public static function provideTriggerData(): array
    {
        return [
            [RouteModeEnum::ROUTE_MODE_SYSTEM_ENTRY],
            [RouteModeEnum::ROUTE_MODE_SYSTEM_EXIT],
            [RouteModeEnum::ROUTE_MODE_WORMHOLE_ENTRY],
            [RouteModeEnum::ROUTE_MODE_WORMHOLE_EXIT],
            [RouteModeEnum::ROUTE_MODE_TRANSWARP],
            [RouteModeEnum::ROUTE_MODE_FLIGHT, MapInterface::class],
            [RouteModeEnum::ROUTE_MODE_FLIGHT, StarSystemMapInterface::class, 2, null, 2],
            [RouteModeEnum::ROUTE_MODE_FLIGHT, StarSystemMapInterface::class, 2, 3, 5]
        ];
    }

    /**
     * @dataProvider provideTriggerData
     */
    public function testTrigger(
        RouteModeEnum $routeMode,
        ?string $nextWaypointClass = null,
        ?int $flightCost = null,
        ?int $tractorCost = null,
        ?int $expectedCost = null
    ): void {
        $messages = $this->mock(MessageCollectionInterface::class);
        $epsSystem = $this->mock(EpsSystemData::class);

        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($epsSystem);

        $this->flightRoute->shouldReceive('getRouteMode')
            ->withNoArgs()
            ->once()
            ->andReturn($routeMode);

        if ($routeMode === RouteModeEnum::ROUTE_MODE_FLIGHT) {
            $this->flightRoute->shouldReceive('getNextWaypoint')
                ->withNoArgs()
                ->once()
                ->andReturn($this->mock($nextWaypointClass));
        }

        if ($flightCost !== null) {
            $this->ship->shouldReceive('getRump->getFlightEcost')
                ->withNoArgs()
                ->once()
                ->andReturn($flightCost);
        }
        if ($tractorCost !== null) {
            $tractoredShip = $this->mock(ShipInterface::class);

            $this->ship->shouldReceive('getTractoredShip')
                ->withNoArgs()
                ->once()
                ->andReturn($tractoredShip);

            $tractoredShip->shouldReceive('getRump->getFlightEcost')
                ->withNoArgs()
                ->once()
                ->andReturn($tractorCost);
        } else {
            $this->ship->shouldReceive('getTractoredShip')
                ->withNoArgs()
                ->andReturn(null);
        }

        if ($expectedCost !== null) {
            $epsSystem->shouldReceive('lowerEps')
                ->with($expectedCost)
                ->once()
                ->andReturnSelf();
            $epsSystem->shouldReceive('update')
                ->withNoArgs()
                ->once();
        }

        $this->subject->trigger(
            $this->wrapper,
            $this->flightRoute,
            $messages
        );
    }
}
