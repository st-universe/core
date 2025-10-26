<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\Flight;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Spacecraft\System\Data\EpsSystemData;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\FlightConsequenceInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\RouteModeEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\StarSystemMap;
use Stu\StuTestCase;

class EpsConsequenceTest extends StuTestCase
{
    private FlightConsequenceInterface $subject;

    private MockInterface&Ship $ship;

    private MockInterface&ShipWrapperInterface $wrapper;

    private MockInterface&FlightRouteInterface $flightRoute;

    #[\Override]
    protected function setUp(): void
    {
        $this->ship = $this->mock(Ship::class);
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

    public function testTriggerExpectNothingWhenShipTractored(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);

        $this->ship->shouldReceive('getCondition->isDestroyed')
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
            [RouteModeEnum::SYSTEM_ENTRY],
            [RouteModeEnum::SYSTEM_EXIT],
            [RouteModeEnum::WORMHOLE_ENTRY],
            [RouteModeEnum::WORMHOLE_EXIT],
            [RouteModeEnum::TRANSWARP],
            [RouteModeEnum::FLIGHT, Map::class],
            [RouteModeEnum::FLIGHT, StarSystemMap::class, 2, null, 2],
            [RouteModeEnum::FLIGHT, StarSystemMap::class, 2, 3, 5]
        ];
    }

    #[DataProvider('provideTriggerData')]
    public function testTrigger(
        RouteModeEnum $routeMode,
        ?string $nextWaypointClass = null,
        ?int $flightCost = null,
        ?int $tractorCost = null,
        ?int $expectedCost = null
    ): void {
        $messages = $this->mock(MessageCollectionInterface::class);
        $epsSystem = $this->mock(EpsSystemData::class);

        $this->ship->shouldReceive('getCondition->isDestroyed')
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

        if ($routeMode === RouteModeEnum::FLIGHT) {
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
            $tractoredShip = $this->mock(Ship::class);

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
