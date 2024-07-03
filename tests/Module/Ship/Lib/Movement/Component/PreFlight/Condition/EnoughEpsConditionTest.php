<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\PreFlight\Condition;

use Override;
use Mockery\MockInterface;
use Stu\Component\Ship\System\Data\EpsSystemData;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\Movement\Component\PreFlight\ConditionCheckResult;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\Movement\Route\RouteModeEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\StuTestCase;

class EnoughEpsConditionTest extends StuTestCase
{
    /** @var MockInterface&ShipSystemManagerInterface */
    private MockInterface $shipSystemManager;

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
        $this->shipSystemManager = $this->mock(ShipSystemManagerInterface::class);

        $this->ship = $this->mock(ShipInterface::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->flightRoute = $this->mock(FlightRouteInterface::class);
        $this->conditionCheckResult = $this->mock(ConditionCheckResult::class);

        $this->wrapper->shouldReceive('get')
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->subject = new EnoughEpsCondition($this->shipSystemManager);
    }

    public static function provideCheckWhenSystemInstalledData(): array
    {
        return [
            [true, false, false, false, ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE],
            [true, false, false, true, ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE],
            [false, true, false, false, ShipSystemTypeEnum::SYSTEM_WARPDRIVE],
            [false, true, false, true, ShipSystemTypeEnum::SYSTEM_WARPDRIVE],
            [false, false, true, false, ShipSystemTypeEnum::SYSTEM_TRANSWARP_COIL]
        ];
    }

    /**
     * @dataProvider provideCheckWhenSystemInstalledData
     */
    public function testCheckWhenSystemInstalled(
        bool $isImpulsNeeded,
        bool $isWarpdriveNeeded,
        bool $isTranswarpNeeded,
        bool $currentSystemState,
        ShipSystemTypeEnum $expectedSystemId
    ): void {
        $epsSystemData = $this->mock(EpsSystemData::class);

        $this->ship->shouldReceive('hasShipSystem')
            ->with($expectedSystemId)
            ->andReturn(true);
        $this->ship->shouldReceive('getSystemState')
            ->with($expectedSystemId)
            ->andReturn($currentSystemState);

        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->andReturn($epsSystemData);

        $this->flightRoute->shouldReceive('isImpulseDriveNeeded')
            ->withNoArgs()
            ->andReturn($isImpulsNeeded);
        $this->flightRoute->shouldReceive('isWarpDriveNeeded')
            ->withNoArgs()
            ->andReturn($isWarpdriveNeeded);
        $this->flightRoute->shouldReceive('isTranswarpCoilNeeded')
            ->withNoArgs()
            ->andReturn($isTranswarpNeeded);
        $this->flightRoute->shouldReceive('getRouteMode')
            ->withNoArgs()
            ->andReturn(RouteModeEnum::ROUTE_MODE_SYSTEM_EXIT);

        $epsSystemData->shouldReceive('getEps')
            ->withNoArgs()
            ->andReturn(42);

        if (!$currentSystemState) {
            $this->shipSystemManager->shouldReceive('getEnergyUsageForActivation')
                ->with($expectedSystemId)
                ->andReturn(42);
        }

        $this->subject->check($this->wrapper, $this->flightRoute, $this->conditionCheckResult);
    }

    public static function provideCheckWhenSystemNotInstalledData(): array
    {
        return [
            [true, false, false,  ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE],
            [false, true, false,  ShipSystemTypeEnum::SYSTEM_WARPDRIVE],
            [false, false, true,  ShipSystemTypeEnum::SYSTEM_TRANSWARP_COIL]
        ];
    }

    /**
     * @dataProvider provideCheckWhenSystemNotInstalledData
     */
    public function testCheckWhenSystemNotInstalled(
        bool $isImpulsNeeded,
        bool $isWarpdriveNeeded,
        bool $isTranswarpNeeded,
        ShipSystemTypeEnum $expectedSystemId
    ): void {
        $this->ship->shouldReceive('hasShipSystem')
            ->with($expectedSystemId)
            ->andReturn(false);

        $this->flightRoute->shouldReceive('isImpulseDriveNeeded')
            ->withNoArgs()
            ->andReturn($isImpulsNeeded);
        $this->flightRoute->shouldReceive('isWarpDriveNeeded')
            ->withNoArgs()
            ->andReturn($isWarpdriveNeeded);
        $this->flightRoute->shouldReceive('isTranswarpCoilNeeded')
            ->withNoArgs()
            ->andReturn($isTranswarpNeeded);
        $this->flightRoute->shouldReceive('getRouteMode')
            ->withNoArgs()
            ->andReturn(RouteModeEnum::ROUTE_MODE_SYSTEM_EXIT);

        $this->subject->check($this->wrapper, $this->flightRoute, $this->conditionCheckResult);
    }

    public static function provideCheckFlightCostForRouteModeData(): array
    {
        return [
            [RouteModeEnum::ROUTE_MODE_SYSTEM_ENTRY],
            [RouteModeEnum::ROUTE_MODE_SYSTEM_EXIT],
            [RouteModeEnum::ROUTE_MODE_WORMHOLE_ENTRY],
            [RouteModeEnum::ROUTE_MODE_WORMHOLE_EXIT],
            [RouteModeEnum::ROUTE_MODE_TRANSWARP],
            [RouteModeEnum::ROUTE_MODE_FLIGHT, MapInterface::class],
            [RouteModeEnum::ROUTE_MODE_FLIGHT, StarSystemMapInterface::class, false],
            [RouteModeEnum::ROUTE_MODE_FLIGHT, StarSystemMapInterface::class, true, 2, null, 2],
            [RouteModeEnum::ROUTE_MODE_FLIGHT, StarSystemMapInterface::class, true, 2, 3, 5]
        ];
    }

    /**
     * @dataProvider provideCheckFlightCostForRouteModeData
     */
    public function testCheckFlightCostForRouteMode(
        RouteModeEnum $routeMode,
        string $nextWaypointClass = null,
        bool $hasImpulse = null,
        int $flightCost = null,
        int $tractorCost = null,
        int $expectedCost = null
    ): void {
        $epsSystem = $this->mock(EpsSystemData::class);

        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->andReturn($epsSystem);

        if ($expectedCost !== null) {
            $epsSystem->shouldReceive('getEps')
                ->withNoArgs()
                ->once()
                ->andReturn($expectedCost);
        }

        $this->flightRoute->shouldReceive('isImpulseDriveNeeded')
            ->withNoArgs()
            ->andReturn(false);
        $this->flightRoute->shouldReceive('isWarpDriveNeeded')
            ->withNoArgs()
            ->andReturn(false);
        $this->flightRoute->shouldReceive('isTranswarpCoilNeeded')
            ->withNoArgs()
            ->andReturn(false);

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

        if ($nextWaypointClass === StarSystemMapInterface::class) {
            $this->ship->shouldReceive('hasShipSystem')
                ->with(ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE)
                ->andReturn($hasImpulse);
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

        $this->subject->check(
            $this->wrapper,
            $this->flightRoute,
            $this->conditionCheckResult
        );
    }

    public function testCheckExpectBlockWhenNotEnoughEpsForFlight(): void
    {
        $epsSystemData = $this->mock(EpsSystemData::class);

        $this->ship->shouldReceive('hasShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE)
            ->andReturn(true);
        $this->ship->shouldReceive('getSystemState')
            ->with(ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE)
            ->andReturn(false);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('SHIP');
        $this->ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->andReturn(false);

        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->andReturn($epsSystemData);

        $this->flightRoute->shouldReceive('isImpulseDriveNeeded')
            ->withNoArgs()
            ->andReturn(true);
        $this->flightRoute->shouldReceive('isWarpDriveNeeded')
            ->withNoArgs()
            ->andReturn(false);
        $this->flightRoute->shouldReceive('isTranswarpCoilNeeded')
            ->withNoArgs()
            ->andReturn(false);
        $this->flightRoute->shouldReceive('getRouteMode')
            ->withNoArgs()
            ->andReturn(RouteModeEnum::ROUTE_MODE_SYSTEM_EXIT);

        $epsSystemData->shouldReceive('getEps')
            ->withNoArgs()
            ->andReturn(0);

        $this->conditionCheckResult->shouldReceive('addBlockedShip')
            ->with(
                $this->ship,
                'Die SHIP hat nicht genug Energie für den Flug (1 benötigt)'
            )
            ->once();

        $this->shipSystemManager->shouldReceive('getEnergyUsageForActivation')
            ->with(ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE)
            ->andReturn(1);

        $this->subject->check($this->wrapper, $this->flightRoute, $this->conditionCheckResult);
    }

    public function testCheckExpectBlockWhenNotEnoughEpsForTractorFlight(): void
    {
        $epsSystemData = $this->mock(EpsSystemData::class);

        $this->ship->shouldReceive('hasShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE)
            ->andReturn(true);
        $this->ship->shouldReceive('getSystemState')
            ->with(ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE)
            ->andReturn(false);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('SHIP');
        $this->ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->andReturn(true);

        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->andReturn($epsSystemData);

        $this->flightRoute->shouldReceive('isImpulseDriveNeeded')
            ->withNoArgs()
            ->andReturn(true);
        $this->flightRoute->shouldReceive('isWarpDriveNeeded')
            ->withNoArgs()
            ->andReturn(false);
        $this->flightRoute->shouldReceive('isTranswarpCoilNeeded')
            ->withNoArgs()
            ->andReturn(false);
        $this->flightRoute->shouldReceive('getRouteMode')
            ->withNoArgs()
            ->andReturn(RouteModeEnum::ROUTE_MODE_SYSTEM_EXIT);

        $epsSystemData->shouldReceive('getEps')
            ->withNoArgs()
            ->andReturn(0);

        $this->conditionCheckResult->shouldReceive('addBlockedShip')
            ->with(
                $this->ship,
                'Die SHIP hat nicht genug Energie für den Traktor-Flug (1 benötigt)'
            )
            ->once();

        $this->shipSystemManager->shouldReceive('getEnergyUsageForActivation')
            ->with(ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE)
            ->andReturn(1);

        $this->subject->check($this->wrapper, $this->flightRoute, $this->conditionCheckResult);
    }
}
