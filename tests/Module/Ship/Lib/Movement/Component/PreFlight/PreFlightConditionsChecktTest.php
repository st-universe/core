<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\PreFlight;

use Mockery;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Ship\Lib\Movement\Component\PreFlight\Condition\PreFlightConditionInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\StuTestCase;

class PreFlightConditionsChecktTest extends StuTestCase
{
    public static function provideCheckPreconditionsData()
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @dataProvider provideCheckPreconditionsData
     */
    public function testCheckPreconditions(bool $isFixedFleetMode): void
    {
        $leaveFleet = $this->mock(LeaveFleetInterface::class);
        $wrapper1 = $this->mock(ShipWrapperInterface::class);
        $wrapper2 = $this->mock(ShipWrapperInterface::class);
        $flightRoute = $this->mock(FlightRouteInterface::class);
        $condition1 = $this->mock(PreFlightConditionInterface::class);
        $condition2 = $this->mock(PreFlightConditionInterface::class);

        $subject = new PreFlightConditionsCheck($leaveFleet, [$condition1, $condition2]);

        $conditionCheckResult = null;

        $condition1->shouldReceive('check')
            ->with($wrapper1, $flightRoute, Mockery::on(function (ConditionCheckResult $ccr) use (&$conditionCheckResult) {
                if ($conditionCheckResult === null) {
                    $conditionCheckResult = $ccr;
                    return true;
                }

                return $ccr === $conditionCheckResult;
            }))
            ->once()
            ->andReturn(false);
        $condition1->shouldReceive('check')
            ->with($wrapper2, $flightRoute, Mockery::on(function (ConditionCheckResult $ccr) use (&$conditionCheckResult) {
                return $ccr === $conditionCheckResult;
            }))
            ->once()
            ->andReturn(false);
        $condition2->shouldReceive('check')
            ->with($wrapper1, $flightRoute, Mockery::on(function (ConditionCheckResult $ccr) use (&$conditionCheckResult) {
                return $ccr === $conditionCheckResult;
            }))
            ->once()
            ->andReturn(false);
        $condition2->shouldReceive('check')
            ->with($wrapper2, $flightRoute, Mockery::on(function (ConditionCheckResult $ccr) use (&$conditionCheckResult) {
                return $ccr === $conditionCheckResult;
            }))
            ->once()
            ->andReturn(false);

        $result = $subject->checkPreconditions([$wrapper1, $wrapper2], $flightRoute, $isFixedFleetMode);

        $this->assertEquals($conditionCheckResult, $result);
    }
}
