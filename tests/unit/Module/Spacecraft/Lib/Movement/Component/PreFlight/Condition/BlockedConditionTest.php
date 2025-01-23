<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\Condition;

use Mockery\MockInterface;
use Override;
use Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\ConditionCheckResult;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\TholianWebInterface;
use Stu\StuTestCase;

class BlockedConditionTest extends StuTestCase
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

        $this->subject = new BlockedCondition();
    }

    public function testCheckExpectNothingWhenNeitherTractoredNorInWeb(): void
    {
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->andReturn(false);
        $this->ship->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->subject->check($this->wrapper, $this->flightRoute, $this->conditionCheckResult);
    }

    public function testCheckExpectBlockWhenTractored(): void
    {
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->andReturn(true);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('SHIP');

        $this->conditionCheckResult->shouldReceive('addBlockedShip')
            ->with(
                $this->ship,
                'Die SHIP wird von einem Traktorstrahl gehalten'
            )
            ->once();

        $this->subject->check($this->wrapper, $this->flightRoute, $this->conditionCheckResult);
    }

    public function testCheckExpectBlockWhenInFinishedWeb(): void
    {
        $web = $this->mock(TholianWebInterface::class);

        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->andReturn(false);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('SHIP');
        $this->ship->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->once()
            ->andReturn($web);

        $web->shouldReceive('isFinished')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->conditionCheckResult->shouldReceive('addBlockedShip')
            ->with(
                $this->ship,
                'Die SHIP wird von einem Energienetz gehalten'
            )
            ->once();

        $this->subject->check($this->wrapper, $this->flightRoute, $this->conditionCheckResult);
    }

    public function testCheckExpectNothingWhenInUnfinishedWeb(): void
    {
        $web = $this->mock(TholianWebInterface::class);

        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->andReturn(false);
        $this->ship->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->once()
            ->andReturn($web);

        $web->shouldReceive('isFinished')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->subject->check($this->wrapper, $this->flightRoute, $this->conditionCheckResult);
    }
}
