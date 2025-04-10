<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight;

use Mockery\MockInterface;
use Override;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Spacecraft\Lib\Movement\FlightCompany;
use Stu\Orm\Entity\ShipInterface;
use Stu\StuTestCase;

class ConditionCheckResultTest extends StuTestCase
{
    /** @var MockInterface&LeaveFleetInterface */
    private $leaveFleet;

    /** @var MockInterface&FlightCompany */
    private $flightCompany;

    #[Override]
    protected function setUp(): void
    {
        $this->leaveFleet = $this->mock(LeaveFleetInterface::class);
        $this->flightCompany = $this->mock(FlightCompany::class);
    }

    public function testAddBlockedShipExpectBlockWhenFixedFleet(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(42);

        $this->flightCompany->shouldReceive('getLeader')
            ->withNoArgs()
            ->andReturn($this->mock(ShipInterface::class));
        $this->flightCompany->shouldReceive('isFixedFleetMode')
            ->withNoArgs()
            ->andReturn(true);

        $subject = new ConditionCheckResult($this->leaveFleet, $this->flightCompany);

        $subject->addBlockedShip($ship, 'REASON');

        $this->assertTrue($subject->isBlocked($ship));
        $this->assertFalse($subject->isFlightPossible());
        $this->assertEquals(['REASON'], $subject->getInformations());
    }

    public function testAddBlockedShipExpectLeaveFleetWhenNotFixedFleet(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(42);
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

        $this->flightCompany->shouldReceive('getLeader')
            ->withNoArgs()
            ->andReturn($this->mock(ShipInterface::class));
        $this->flightCompany->shouldReceive('isFixedFleetMode')
            ->withNoArgs()
            ->andReturn(false);

        $this->leaveFleet->shouldReceive('leaveFleet')
            ->with($ship)
            ->once();

        $subject = new ConditionCheckResult($this->leaveFleet, $this->flightCompany);

        $this->assertFalse($subject->isBlocked($ship));

        $subject->addBlockedShip($ship, 'REASON');
        $subject->addBlockedShip($ship, 'REASON');

        $this->assertTrue($subject->isBlocked($ship));
        $this->assertTrue($subject->isFlightPossible());
        $this->assertEquals(['REASON', 'Die SHIP hat die Flotte verlassen (22|33)'], $subject->getInformations());
    }

    public function testAddBlockedShipExpectNoLeaveFleetWhenNotFixedFleetButLeaderBlocked(): void
    {
        $leaderShip = $this->mock(ShipInterface::class);
        $ship = $this->mock(ShipInterface::class);

        $leaderShip->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(55);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(42);

        $this->flightCompany->shouldReceive('getLeader')
            ->withNoArgs()
            ->andReturn($leaderShip);
        $this->flightCompany->shouldReceive('isFixedFleetMode')
            ->withNoArgs()
            ->andReturn(false);

        $subject = new ConditionCheckResult($this->leaveFleet, $this->flightCompany);

        $this->assertFalse($subject->isBlocked($ship));

        $subject->addBlockedShip($leaderShip, 'LEADER_REASON');
        $subject->addBlockedShip($ship, 'REASON');
        $subject->addBlockedShip($ship, 'REASON');

        $this->assertTrue($subject->isBlocked($ship));
        $this->assertFalse($subject->isFlightPossible());
        $this->assertEquals(['LEADER_REASON', 'REASON'], $subject->getInformations());
    }

    public function testAddBlockedShipExpectNoLeaveFleetWhenLeader(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(42);

        $this->flightCompany->shouldReceive('getLeader')
            ->withNoArgs()
            ->andReturn($ship);
        $this->flightCompany->shouldReceive('isFixedFleetMode')
            ->withNoArgs()
            ->andReturn(false);

        $subject = new ConditionCheckResult($this->leaveFleet, $this->flightCompany);

        $this->assertFalse($subject->isBlocked($ship));

        $subject->addBlockedShip($ship, 'REASON');
        $subject->addBlockedShip($ship, 'REASON');

        $this->assertTrue($subject->isBlocked($ship));
        $this->assertFalse($subject->isFlightPossible());
        $this->assertEquals(['REASON'], $subject->getInformations());
    }

    public function testAddBlockedShipExpectBlockWhenFleetLeader(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(42);

        $this->flightCompany->shouldReceive('getLeader')
            ->withNoArgs()
            ->andReturn($ship);
        $this->flightCompany->shouldReceive('isFixedFleetMode')
            ->withNoArgs()
            ->andReturn(false);

        $subject = new ConditionCheckResult($this->leaveFleet, $this->flightCompany);

        $this->assertFalse($subject->isBlocked($ship));

        $subject->addBlockedShip($ship, 'REASON');

        $this->assertTrue($subject->isBlocked($ship));
        $this->assertFalse($subject->isFlightPossible());
        $this->assertEquals(['REASON'], $subject->getInformations());
    }

    public function testAddBlockedShipExpectBlockWhenSingleShip(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(42);

        $this->flightCompany->shouldReceive('getLeader')
            ->withNoArgs()
            ->andReturn($this->mock(ShipInterface::class));
        $this->flightCompany->shouldReceive('isFixedFleetMode')
            ->withNoArgs()
            ->andReturn(true);

        $subject = new ConditionCheckResult($this->leaveFleet, $this->flightCompany);

        $this->assertFalse($subject->isBlocked($ship));

        $subject->addBlockedShip($ship, 'REASON');

        $this->assertTrue($subject->isBlocked($ship));
        $this->assertFalse($subject->isFlightPossible());
        $this->assertEquals(['REASON'], $subject->getInformations());
    }

    public function testAddBlockedShipExpectBlockWhenSingleShipIsNotFleetLeader(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(42);

        $this->flightCompany->shouldReceive('getLeader')
            ->withNoArgs()
            ->andReturn($ship);
        $this->flightCompany->shouldReceive('isFixedFleetMode')
            ->withNoArgs()
            ->andReturn(false);

        $subject = new ConditionCheckResult($this->leaveFleet, $this->flightCompany);

        $this->assertFalse($subject->isBlocked($ship));

        $subject->addBlockedShip($ship, 'REASON');

        $this->assertTrue($subject->isBlocked($ship));
        $this->assertFalse($subject->isFlightPossible());
        $this->assertEquals(['REASON'], $subject->getInformations());
    }
}
