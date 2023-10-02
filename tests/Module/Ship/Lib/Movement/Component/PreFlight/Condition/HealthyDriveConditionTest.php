<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\PreFlight\Condition;

use Mockery\MockInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\Movement\Component\PreFlight\ConditionCheckResult;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\StuTestCase;

class HealthyDriveConditionTest extends StuTestCase
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

    protected function setUp(): void
    {
        $this->ship = $this->mock(ShipInterface::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->flightRoute = $this->mock(FlightRouteInterface::class);
        $this->conditionCheckResult = $this->mock(ConditionCheckResult::class);

        $this->wrapper->shouldReceive('get')
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->subject = new HealthyDriveCondition();
    }

    public static function provideCheckData()
    {
        return [
            [true, false, false, ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE, false],
            [true, false, false, ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE, true, false],
            [true, false, false, ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE, true, true],
            [false, true, false,  ShipSystemTypeEnum::SYSTEM_WARPDRIVE, false],
            [false, true, false,  ShipSystemTypeEnum::SYSTEM_WARPDRIVE, true, false],
            [false, true, false,  ShipSystemTypeEnum::SYSTEM_WARPDRIVE, true, true],
            [false, false, true,  ShipSystemTypeEnum::SYSTEM_TRANSWARP_COIL, false],
            [false, false, true,  ShipSystemTypeEnum::SYSTEM_TRANSWARP_COIL, true, false],
            [false, false, true,  ShipSystemTypeEnum::SYSTEM_TRANSWARP_COIL, true, true]
        ];
    }

    /**
     * @dataProvider provideCheckData
     */
    public function testCheck(
        bool $isImpulsNeeded,
        bool $isWarpdriveNeeded,
        bool $isTranswarpNeeded,
        int $systemId,
        bool $hasShipSystem,
        bool $isSystemHealthy = null
    ): void {

        $this->ship->shouldReceive('hasShipSystem')
            ->with($systemId)
            ->andReturn($hasShipSystem);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('SHIP');

        if ($isSystemHealthy !== null) {
            $this->ship->shouldReceive('isSystemHealthy')
                ->with($systemId)
                ->andReturn($isSystemHealthy);
        }

        $this->flightRoute->shouldReceive('isImpulseDriveNeeded')
            ->withNoArgs()
            ->andReturn($isImpulsNeeded);
        $this->flightRoute->shouldReceive('isWarpDriveNeeded')
            ->withNoArgs()
            ->andReturn($isWarpdriveNeeded);
        $this->flightRoute->shouldReceive('isTranswarpCoilNeeded')
            ->withNoArgs()
            ->andReturn($isTranswarpNeeded);

        if (!$hasShipSystem) {
            $this->conditionCheckResult->shouldReceive('addBlockedShip')
                ->with(
                    $this->ship,
                    sprintf(
                        'Die SHIP verfügt über keine(n) %s',
                        ShipSystemTypeEnum::getDescription($systemId)
                    )
                )
                ->once();
        }

        if ($isSystemHealthy === false) {
            $this->conditionCheckResult->shouldReceive('addBlockedShip')
                ->with(
                    $this->ship,
                    sprintf(
                        'Die SHIP kann das System %s nicht aktivieren',
                        ShipSystemTypeEnum::getDescription($systemId)
                    )
                )
                ->once();
        }

        $this->subject->check($this->wrapper, $this->flightRoute, $this->conditionCheckResult);
    }
}
