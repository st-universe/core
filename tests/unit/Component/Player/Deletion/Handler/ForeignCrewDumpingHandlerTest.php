<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\MockInterface;
use Stu\Module\Spacecraft\Lib\Crew\SpacecraftLeaverInterface;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Station;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\StationRepositoryInterface;
use Stu\StuTestCase;

class ForeignCrewDumpingHandlerTest extends StuTestCase
{
    private MockInterface&StationRepositoryInterface $stationRepository;
    private MockInterface&SpacecraftLeaverInterface $spacecraftLeaver;
    private MockInterface&EntityManagerInterface $entityManager;

    private PlayerDeletionHandlerInterface $handler;

    #[\Override]
    public function setUp(): void
    {
        $this->stationRepository = $this->mock(StationRepositoryInterface::class);
        $this->spacecraftLeaver = $this->mock(SpacecraftLeaverInterface::class);
        $this->entityManager = $this->mock(EntityManagerInterface::class);

        $this->handler = new ForeignCrewDumpingHandler(
            $this->stationRepository,
            $this->spacecraftLeaver,
            $this->entityManager
        );
    }

    public function testDelete(): void
    {
        $stationWithoutCrew = $this->mock(Station::class);
        $stationWithForeigner = $this->mock(Station::class);
        $ownCrewAssignment = $this->mock(CrewAssignment::class);
        $foreignerCrewAssignment = $this->mock(CrewAssignment::class);
        $ownCrew = $this->mock(Crew::class);
        $foreignerCrew = $this->mock(Crew::class);
        $user = $this->mock(User::class);
        $otherUser = $this->mock(User::class);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(1111);
        $otherUser->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(2222);

        $stationWithoutCrew->shouldReceive('getCrewAssignments')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection());
        $stationWithForeigner->shouldReceive('getCrewAssignments')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$ownCrewAssignment, $foreignerCrewAssignment]));

        $ownCrewAssignment->shouldReceive('getCrew')
            ->withNoArgs()
            ->once()
            ->andReturn($ownCrew);
        $foreignerCrewAssignment->shouldReceive('getCrew')
            ->withNoArgs()
            ->once()
            ->andReturn($foreignerCrew);

        $ownCrew->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $foreignerCrew->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($otherUser);

        $foreignerCrew->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('CREW');

        $stationWithForeigner->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('STATION');
        $stationWithForeigner->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);

        $user->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('SPIELER');

        $this->stationRepository->shouldReceive('getStationsByUser')
            ->with(1111)
            ->once()
            ->andReturn([$stationWithoutCrew, $stationWithForeigner]);

        $this->spacecraftLeaver->shouldReceive('dumpCrewman')
            ->with(
                $foreignerCrewAssignment,
                'Die Dienste von Crewman CREW werden nicht mehr auf der Station STATION von Spieler SPIELER benötigt.'
            )
            ->once();

        $this->entityManager->shouldReceive('detach')
            ->with($foreignerCrewAssignment)
            ->once();
        $this->entityManager->shouldReceive('detach')
            ->with($foreignerCrew)
            ->once();

        $this->handler->delete($user);
    }
}
