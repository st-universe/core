<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\PreFlight\Condition;

use Mockery\MockInterface;
use Stu\Module\Ship\Lib\Movement\Component\PreFlight\ConditionCheckResult;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\StuTestCase;

class CrewConditionTest extends StuTestCase
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

        $this->subject = new CrewCondition();
    }

    public function testCheckExpectNothingWhenEnoughCrew(): void
    {
        $this->ship->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->andReturn(true);

        $this->subject->check($this->wrapper, $this->flightRoute, $this->conditionCheckResult);
    }

    public function testCheckBlockWhenNotEnoughCrew(): void
    {
        $this->ship->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->andReturn(false);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('SHIP');

        $this->conditionCheckResult->shouldReceive('addBlockedShip')
            ->with(
                $this->ship,
                'Die SHIP hat ungenügend Crew'
            )
            ->once();

        $this->subject->check($this->wrapper, $this->flightRoute, $this->conditionCheckResult);
    }
}
