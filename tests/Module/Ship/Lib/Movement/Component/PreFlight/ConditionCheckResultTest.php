<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\PreFlight;

use Mockery\MockInterface;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\StuTestCase;

class ConditionCheckResultTest extends StuTestCase
{
    /** @var MockInterface&LeaveFleetInterface */
    private MockInterface $leaveFleet;

    /** @var MockInterface&ShipWrapperInterface */
    private MockInterface $leader;

    protected function setUp(): void
    {
        $this->leaveFleet = $this->mock(LeaveFleetInterface::class);
        $this->leader = $this->mock(ShipWrapperInterface::class);

        $this->ship = $this->mock(ShipInterface::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->flightRoute = $this->mock(FlightRouteInterface::class);
    }

    public function testAddBlockedShipExpectBlockWhenFixedFleet(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(42);
        $ship->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $subject = new ConditionCheckResult($this->leaveFleet, $this->leader, true);

        $subject->addBlockedShip($ship, 'REASON');

        $this->assertFalse($subject->isNotBlocked($ship));
        $this->assertFalse($subject->isFlightPossible());
        $this->assertEquals(['REASON'], $subject->getInformations());
    }

    public function testAddBlockedShipExpectLeaveFleetWhenNotFixedFleet(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(42);
        $ship->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('SHIP');
        $ship->shouldReceive('getPosX')
            ->withNoArgs()
            ->once()
            ->andReturn(22);
        $ship->shouldReceive('getPosY')
            ->withNoArgs()
            ->once()
            ->andReturn(33);

        $this->leader->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(ShipInterface::class));

        $this->leaveFleet->shouldReceive('leaveFleet')
            ->with($ship)
            ->once();

        $subject = new ConditionCheckResult($this->leaveFleet, $this->leader, false);

        $this->assertTrue($subject->isNotBlocked($ship));

        $subject->addBlockedShip($ship, 'REASON');
        $subject->addBlockedShip($ship, 'REASON');

        $this->assertFalse($subject->isNotBlocked($ship));
        $this->assertTrue($subject->isFlightPossible());
        $this->assertEquals(['REASON', 'Die SHIP hat die Flotte verlassen (22|33)'], $subject->getInformations());
    }

    public function testAddBlockedShipExpectNoLeaveFleetWhenLeader(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(42);
        $ship->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->leader->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($ship);

        $subject = new ConditionCheckResult($this->leaveFleet, $this->leader, false);

        $this->assertTrue($subject->isNotBlocked($ship));

        $subject->addBlockedShip($ship, 'REASON');
        $subject->addBlockedShip($ship, 'REASON');

        $this->assertFalse($subject->isNotBlocked($ship));
        $this->assertTrue($subject->isFlightPossible());
        $this->assertEquals(['REASON'], $subject->getInformations());
    }

    public function testAddBlockedShipExpectBlockWhenFleetLeader(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(42);
        $ship->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $subject = new ConditionCheckResult($this->leaveFleet, $this->leader, false);

        $this->assertTrue($subject->isNotBlocked($ship));

        $subject->addBlockedShip($ship, 'REASON');

        $this->assertFalse($subject->isNotBlocked($ship));
        $this->assertFalse($subject->isFlightPossible());
        $this->assertEquals(['REASON'], $subject->getInformations());
    }
}
