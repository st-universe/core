<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Route;

use Mockery\MockInterface;
use Stu\Lib\InformationWrapper;
use Stu\Module\Control\StuTime;
use Stu\Module\Ship\Lib\Movement\Component\CheckAstronomicalWaypoint;
use Stu\Module\Ship\Lib\Movement\Component\FlightSignatureCreatorInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\MapRegionInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\WormholeEntryInterface;
use Stu\Orm\Repository\WormholeEntryRepositoryInterface;
use Stu\StuTestCase;

class EnterWaypointTest extends StuTestCase
{
    /** @var MockInterface&FlightSignatureCreatorInterface */
    private MockInterface $flightSignatureCreator;

    /** @var MockInterface&UpdateFlightDirectionInterface */
    private MockInterface $updateFlightDirection;

    /** @var MockInterface&WormholeEntryRepositoryInterface */
    private MockInterface $wormholeEntryRepository;

    /** @var MockInterface&CheckAstronomicalWaypointInterface */
    private MockInterface $checkAstronomicalWaypoint;

    /** @var MockInterface&StuTime */
    private MockInterface $stuTime;

    private EnterWaypointInterface $subject;

    protected function setUp(): void
    {
        $this->flightSignatureCreator = $this->mock(FlightSignatureCreatorInterface::class);
        $this->updateFlightDirection = $this->mock(UpdateFlightDirectionInterface::class);
        $this->wormholeEntryRepository = $this->mock(WormholeEntryRepositoryInterface::class);
        $this->checkAstronomicalWaypoint = $this->mock(CheckAstronomicalWaypoint::class);
        $this->stuTime = $this->mock(StuTime::class);

        $this->subject = new EnterWaypoint(
            $this->flightSignatureCreator,
            $this->updateFlightDirection,
            $this->wormholeEntryRepository,
            $this->checkAstronomicalWaypoint,
            $this->stuTime
        );
    }

    public function testEnterNextWaypointExpectLocationUpdateWhenOnMap()
    {
        $ship = $this->mock(ShipInterface::class);
        $oldWaypoint = $this->mock(StarSystemMapInterface::class);
        $waypoint = $this->mock(MapInterface::class);
        $informations = $this->mock(InformationWrapper::class);

        $ship->shouldReceive('getCurrentMapField')
            ->withNoArgs()
            ->once()
            ->andReturn($oldWaypoint);
        $ship->shouldReceive('updateLocation')
            ->with($waypoint, null)
            ->once();
        $ship->shouldReceive('getSystem')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $ship->shouldReceive('getMapRegion')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(MapRegionInterface::class));

        $oldWaypoint->shouldReceive('getSystem->isWormhole')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->updateFlightDirection->shouldReceive('updateWhenSystemExit')
            ->with($ship, $oldWaypoint)
            ->once();

        $this->checkAstronomicalWaypoint->shouldReceive('checkWaypoint')
            ->with($ship, $informations)
            ->once();

        $this->subject->enterNextWaypoint(
            $ship,
            false,
            $waypoint,
            null,
            $informations
        );
    }

    public function testEnterNextWaypointExpectWormholeEntryUsing()
    {
        $ship = $this->mock(ShipInterface::class);
        $oldWaypoint = $this->mock(StarSystemMapInterface::class);
        $waypoint = $this->mock(MapInterface::class);
        $informations = $this->mock(InformationWrapper::class);
        $wormholeEntry = $this->mock(WormholeEntryInterface::class);

        $ship->shouldReceive('getCurrentMapField')
            ->withNoArgs()
            ->once()
            ->andReturn($oldWaypoint);
        $ship->shouldReceive('updateLocation')
            ->with($waypoint, null)
            ->once();
        $ship->shouldReceive('getSystem')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $ship->shouldReceive('getMapRegion')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $oldWaypoint->shouldReceive('getSystem->isWormhole')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

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
            $wormholeEntry,
            $informations
        );
    }

    public function testEnterNextWaypointExpectLocationUpdateWhenOnSystemMap()
    {
        $ship = $this->mock(ShipInterface::class);
        $oldWaypoint = $this->mock(StarSystemMapInterface::class);
        $waypoint = $this->mock(StarSystemMapInterface::class);
        $informations = $this->mock(InformationWrapper::class);

        $ship->shouldReceive('getCurrentMapField')
            ->withNoArgs()
            ->once()
            ->andReturn($oldWaypoint);
        $ship->shouldReceive('updateLocation')
            ->with(null, $waypoint)
            ->once();
        $ship->shouldReceive('getSystem')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(StarSystemInterface::class));

        $waypoint->shouldReceive('getSystem->isWormhole')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->checkAstronomicalWaypoint->shouldReceive('checkWaypoint')
            ->with($ship, $informations)
            ->once();

        $this->subject->enterNextWaypoint(
            $ship,
            false,
            $waypoint,
            null,
            $informations
        );
    }

    public function testEnterNextWaypointExpectSettingCxAndCyToZeroWhenEnteringWormhole()
    {
        $ship = $this->mock(ShipInterface::class);
        $oldWaypoint = $this->mock(StarSystemMapInterface::class);
        $waypoint = $this->mock(StarSystemMapInterface::class);
        $informations = $this->mock(InformationWrapper::class);

        $ship->shouldReceive('getCurrentMapField')
            ->withNoArgs()
            ->once()
            ->andReturn($oldWaypoint);
        $ship->shouldReceive('updateLocation')
            ->with(null, $waypoint)
            ->once();
        $ship->shouldReceive('setCx')
            ->with(0)
            ->once();
        $ship->shouldReceive('setCy')
            ->with(0)
            ->once();
        $ship->shouldReceive('getSystem')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(StarSystemInterface::class));

        $waypoint->shouldReceive('getSystem->isWormhole')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->checkAstronomicalWaypoint->shouldReceive('checkWaypoint')
            ->with($ship,  $informations)
            ->once();

        $this->subject->enterNextWaypoint(
            $ship,
            false,
            $waypoint,
            null,
            $informations
        );
    }

    public function testEnterNextWaypointExpectFlightDirectionUpdateAndSignatureCreationWhenTraversing()
    {
        $ship = $this->mock(ShipInterface::class);
        $oldWaypoint = $this->mock(StarSystemMapInterface::class);
        $waypoint = $this->mock(StarSystemMapInterface::class);
        $informations = $this->mock(InformationWrapper::class);

        $ship->shouldReceive('updateLocation')
            ->with(null, $waypoint)
            ->once();
        $ship->shouldReceive('getCurrentMapField')
            ->withNoArgs()
            ->once()
            ->andReturn($oldWaypoint);
        $ship->shouldReceive('getSystem')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(StarSystemInterface::class));

        $waypoint->shouldReceive('getSystem->isWormhole')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->updateFlightDirection->shouldReceive('updateWhenTraversing')
            ->with($oldWaypoint, $waypoint, $ship)
            ->once()
            ->andReturn(42);

        $this->flightSignatureCreator->shouldReceive('createSignatures')
            ->with($ship, 42, $oldWaypoint, $waypoint)
            ->once();

        $this->checkAstronomicalWaypoint->shouldReceive('checkWaypoint')
            ->with($ship,  $informations)
            ->once();

        $this->subject->enterNextWaypoint(
            $ship,
            true,
            $waypoint,
            null,
            $informations
        );
    }
}
