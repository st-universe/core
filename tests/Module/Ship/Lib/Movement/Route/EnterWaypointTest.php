<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Route;

use Mockery\MockInterface;
use Stu\Module\Control\StuTime;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\WormholeEntryInterface;
use Stu\Orm\Repository\WormholeEntryRepositoryInterface;
use Stu\StuTestCase;

class EnterWaypointTest extends StuTestCase
{
    /** @var MockInterface&WormholeEntryRepositoryInterface */
    private MockInterface $wormholeEntryRepository;

    /** @var MockInterface&StuTime */
    private MockInterface $stuTime;

    private EnterWaypointInterface $subject;

    protected function setUp(): void
    {
        $this->wormholeEntryRepository = $this->mock(WormholeEntryRepositoryInterface::class);
        $this->stuTime = $this->mock(StuTime::class);

        $this->subject = new EnterWaypoint(
            $this->wormholeEntryRepository,
            $this->stuTime
        );
    }

    public function testEnterNextWaypointExpectLocationUpdateWhenOnMap()
    {
        $ship = $this->mock(ShipInterface::class);
        $waypoint = $this->mock(MapInterface::class);

        $ship->shouldReceive('updateLocation')
            ->with($waypoint)
            ->once();

        $this->subject->enterNextWaypoint(
            $ship,
            false,
            $waypoint,
            null
        );
    }

    public function testEnterNextWaypointExpectWormholeEntryUsing()
    {
        $ship = $this->mock(ShipInterface::class);
        $waypoint = $this->mock(MapInterface::class);
        $wormholeEntry = $this->mock(WormholeEntryInterface::class);

        $ship->shouldReceive('updateLocation')
            ->with($waypoint)
            ->once();

        $this->stuTime->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $wormholeEntry->shouldReceive('setLastUsed')
            ->with(42)
            ->once();

        $this->wormholeEntryRepository->shouldReceive('save')
            ->with($wormholeEntry)
            ->once();

        $this->subject->enterNextWaypoint(
            $ship,
            false,
            $waypoint,
            $wormholeEntry
        );
    }

    public function testEnterNextWaypointExpectLocationUpdateWhenOnSystemMap()
    {
        $ship = $this->mock(ShipInterface::class);
        $waypoint = $this->mock(StarSystemMapInterface::class);

        $ship->shouldReceive('updateLocation')
            ->with($waypoint)
            ->once();

        $waypoint->shouldReceive('getSystem->isWormhole')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->subject->enterNextWaypoint(
            $ship,
            false,
            $waypoint,
            null
        );
    }

    public function testEnterNextWaypointExpectSettingCxAndCyToZeroWhenEnteringWormhole()
    {
        $ship = $this->mock(ShipInterface::class);
        $waypoint = $this->mock(StarSystemMapInterface::class);

        $ship->shouldReceive('updateLocation')
            ->with($waypoint)
            ->once();
        $ship->shouldReceive('setCx')
            ->with(0)
            ->once();
        $ship->shouldReceive('setCy')
            ->with(0)
            ->once();

        $waypoint->shouldReceive('getSystem->isWormhole')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->subject->enterNextWaypoint(
            $ship,
            false,
            $waypoint,
            null
        );
    }

    public function testEnterNextWaypointExpectFlightDirectionUpdateAndSignatureCreationWhenTraversing()
    {
        $ship = $this->mock(ShipInterface::class);
        $waypoint = $this->mock(StarSystemMapInterface::class);

        $ship->shouldReceive('updateLocation')
            ->with($waypoint)
            ->once();

        $waypoint->shouldReceive('getSystem->isWormhole')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->subject->enterNextWaypoint(
            $ship,
            true,
            $waypoint,
            null
        );
    }
}
