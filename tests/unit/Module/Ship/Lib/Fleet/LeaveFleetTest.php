<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Fleet;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\Ship;
use Stu\StuTestCase;

class LeaveFleetTest extends StuTestCase
{
    private MockInterface&ChangeFleetLeaderInterface $changeFleetLeader;

    private MockInterface&Ship $ship;

    private LeaveFleetInterface $subject;

    #[\Override]
    public function setUp(): void
    {
        //injected
        $this->changeFleetLeader = $this->mock(ChangeFleetLeaderInterface::class);

        //params
        $this->ship = $this->mock(Ship::class);

        $this->subject = new LeaveFleet(
            $this->changeFleetLeader
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
        $fleet = $this->mock(Fleet::class);

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

        $result = $this->subject->leaveFleet($this->ship);

        $this->assertTrue($result);
    }

    public function testLeaveFleetExpectRemoveWhenInFleet(): void
    {
        $fleet = $this->mock(Fleet::class);

        $fleet->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(43);
        $fleet->shouldReceive('getShips')
            ->withNoArgs()
            ->andReturn(new ArrayCollection());

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

        $result = $this->subject->leaveFleet($this->ship);

        $this->assertTrue($result);
    }
}
