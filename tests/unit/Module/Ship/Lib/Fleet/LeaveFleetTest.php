<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Fleet;

use Mockery\MockInterface;
use Override;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\StuTestCase;

class LeaveFleetTest extends StuTestCase
{
    private MockInterface&ShipRepositoryInterface $shipRepository;

    private MockInterface&ChangeFleetLeaderInterface $changeFleetLeader;

    private MockInterface&ShipInterface $ship;

    private LeaveFleetInterface $subject;

    #[Override]
    public function setUp(): void
    {
        //injected
        $this->shipRepository = $this->mock(ShipRepositoryInterface::class);
        $this->changeFleetLeader = $this->mock(ChangeFleetLeaderInterface::class);

        //params
        $this->ship = $this->mock(ShipInterface::class);

        $this->subject = new LeaveFleet(
            $this->shipRepository,
            $this->changeFleetLeader,
            $this->initLoggerUtil()
        );
    }

    public function testLeaveFleetExpectFalseWhenNotInFleet(): void
    {
        $this->ship->shouldReceive('getFleet')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $result = $this->subject->leaveFleet($this->ship);

        $this->assertFalse($result);
    }

    public function testLeaveFleetExpectFleetLeaderChangeWhenFleetLeader(): void
    {
        $fleet = $this->mock(FleetInterface::class);

        $fleet->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(43);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(42);
        $this->ship->shouldReceive('getFleet')
            ->withNoArgs()
            ->once()
            ->andReturn($fleet);
        $this->ship->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->changeFleetLeader->shouldReceive('change')
            ->with($this->ship)
            ->once();

        $this->shipRepository->shouldReceive('save')
            ->with($this->ship)
            ->once();

        $result = $this->subject->leaveFleet($this->ship);

        $this->assertTrue($result);
    }

    public function testLeaveFleetExpectRemoveWhenInFleet(): void
    {
        $fleet = $this->mock(FleetInterface::class);

        $fleet->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(43);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(42);
        $this->ship->shouldReceive('getFleet')
            ->withNoArgs()
            ->once()
            ->andReturn($fleet);
        $this->ship->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('setFleet')
            ->with(null)
            ->once();
        $this->ship->shouldReceive('setIsFleetLeader')
            ->with(false)
            ->once();
        $this->ship->shouldReceive('setFleetId')
            ->with(null)
            ->once();

        $fleet->shouldReceive('getShips->removeElement')
            ->with($this->ship)
            ->once();

        $this->shipRepository->shouldReceive('save')
            ->with($this->ship)
            ->once();

        $result = $this->subject->leaveFleet($this->ship);

        $this->assertTrue($result);
    }
}
