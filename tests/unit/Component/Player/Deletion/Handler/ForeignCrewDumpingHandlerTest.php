<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\MockInterface;
use Override;
use Stu\Module\Spacecraft\Lib\Crew\SpacecraftLeaverInterface;
use Stu\Orm\Entity\CrewAssignmentInterface;
use Stu\Orm\Entity\CrewInterface;
use Stu\Orm\Entity\StationInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\StationRepositoryInterface;
use Stu\StuTestCase;

class ForeignCrewDumpingHandlerTest extends StuTestCase
{
    /** @var MockInterface&StationRepositoryInterface */
    private $stationRepository;
    /** @var MockInterface&SpacecraftLeaverInterface */
    private $spacecraftLeaver;
    /** @var MockInterface&EntityManagerInterface */
    private $entityManager;

    private PlayerDeletionHandlerInterface $handler;

    #[Override]
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
        $stationWithoutCrew = $this->mock(StationInterface::class);
        $stationWithForeigner = $this->mock(StationInterface::class);
        $ownCrewAssignment = $this->mock(CrewAssignmentInterface::class);
        $foreignerCrewAssignment = $this->mock(CrewAssignmentInterface::class);
        $ownCrew = $this->mock(CrewInterface::class);
        $foreignerCrew = $this->mock(CrewInterface::class);
        $user = $this->mock(UserInterface::class);
        $otherUser = $this->mock(UserInterface::class);

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

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $user->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('SPIELER');

        $this->stationRepository->shouldReceive('getStationsByUser')
            ->with(1)
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
