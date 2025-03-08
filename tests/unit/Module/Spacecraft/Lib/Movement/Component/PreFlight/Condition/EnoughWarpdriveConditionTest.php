<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\Condition;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Spacecraft\System\Data\WarpDriveSystemData;
use Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\ConditionCheckResult;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\RouteModeEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\StuTestCase;

class EnoughWarpdriveConditionTest extends StuTestCase
{
    private PreFlightConditionInterface $subject;

    /** @var MockInterface&ShipInterface */
    private MockInterface $ship;

    /** @var MockInterface&ShipWrapperInterface */
    private MockInterface $wrapper;

    /** @var MockInterface&FlightRouteInterface */
    private MockInterface $flightRoute;

    /** @var MockInterface&ConditionCheckResult */
    private MockInterface $conditionCheckResult;

    #[Override]
    protected function setUp(): void
    {
        $this->ship = $this->mock(ShipInterface::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->flightRoute = $this->mock(FlightRouteInterface::class);
        $this->conditionCheckResult = $this->mock(ConditionCheckResult::class);

        $this->wrapper->shouldReceive('get')
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->subject = new EnoughWarpdriveCondition();
    }

    public static function provideCheckWarpdriveWhenEnoughWarpdriveData(): array
    {
        return [
            [RouteModeEnum::SYSTEM_ENTRY],
            [RouteModeEnum::SYSTEM_EXIT],
            [RouteModeEnum::WORMHOLE_ENTRY],
            [RouteModeEnum::WORMHOLE_EXIT],
            [RouteModeEnum::TRANSWARP],
            [RouteModeEnum::FLIGHT, StarSystemMapInterface::class],
            [RouteModeEnum::FLIGHT, MapInterface::class, false],
            [RouteModeEnum::FLIGHT, MapInterface::class, true, false, 1],
            [RouteModeEnum::FLIGHT, MapInterface::class, true, true, 3]
        ];
    }

    #[DataProvider('provideCheckWarpdriveWhenEnoughWarpdriveData')]
    public function testCheckWarpdriveWhenEnoughWarpdrive(
        RouteModeEnum $routeMode,
        ?string $nextWaypointClass = null,
        ?bool $hasWarpdrive = null,
        ?bool $isTractoring = null,
        ?int $expectedCost = null
    ): void {
        $warpdriveSystem = $this->mock(WarpDriveSystemData::class);

        $this->wrapper->shouldReceive('getWarpDriveSystemData')
            ->withNoArgs()
            ->andReturn($hasWarpdrive ? $warpdriveSystem : null);

        if ($expectedCost !== null) {
            $warpdriveSystem->shouldReceive('getWarpDrive')
                ->withNoArgs()
                ->once()
                ->andReturn($expectedCost);
        }

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

        if ($hasWarpdrive) {
            $this->ship->shouldReceive('isTractoring')
                ->withNoArgs()
                ->andReturn($isTractoring);
        }

        $this->subject->check(
            $this->wrapper,
            $this->flightRoute,
            $this->conditionCheckResult
        );
    }

    public static function provideCheckWarpdriveWhenNotEnoughWarpdriveData(): array
    {
        return [
            [RouteModeEnum::SYSTEM_ENTRY],
            [RouteModeEnum::SYSTEM_EXIT],
            [RouteModeEnum::WORMHOLE_ENTRY],
            [RouteModeEnum::WORMHOLE_EXIT],
            [RouteModeEnum::TRANSWARP],
            [RouteModeEnum::FLIGHT, StarSystemMapInterface::class],
            [RouteModeEnum::FLIGHT, MapInterface::class, false],
            [RouteModeEnum::FLIGHT, MapInterface::class, true, false, 1],
            [RouteModeEnum::FLIGHT, MapInterface::class, true, true, 3]
        ];
    }

    #[DataProvider('provideCheckWarpdriveWhenNotEnoughWarpdriveData')]
    public function testCheckWarpdriveWhenNotEnoughWarpdrive(
        RouteModeEnum $routeMode,
        ?string $nextWaypointClass = null,
        ?bool $hasWarpdrive = null,
        ?bool $isTractoring = null,
        ?int $expectedCost = null
    ): void {
        $warpdriveSystem = $this->mock(WarpDriveSystemData::class);

        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('SHIP');

        $this->wrapper->shouldReceive('getWarpDriveSystemData')
            ->withNoArgs()
            ->andReturn($hasWarpdrive ? $warpdriveSystem : null);

        if ($expectedCost !== null) {
            $warpdriveSystem->shouldReceive('getWarpDrive')
                ->withNoArgs()
                ->once()
                ->andReturn($expectedCost - 1);
        }

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

        if ($hasWarpdrive) {
            $this->ship->shouldReceive('isTractoring')
                ->withNoArgs()
                ->andReturn($isTractoring);

            $this->conditionCheckResult->shouldReceive('addBlockedShip')
                ->with(
                    $this->ship,
                    sprintf(
                        'Die SHIP hat nicht genug Warpantriebsenergie für den %s (%d benötigt)',
                        $isTractoring ? 'Traktor-Flug' : 'Flug',
                        $expectedCost
                    )
                )
                ->once();
        }


        $this->subject->check(
            $this->wrapper,
            $this->flightRoute,
            $this->conditionCheckResult
        );
    }
}
