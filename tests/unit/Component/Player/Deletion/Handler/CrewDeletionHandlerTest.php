<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\MockInterface;
use Override;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\StuTestCase;

class CrewDeletionHandlerTest extends StuTestCase
{
    /** @var MockInterface&CrewAssignmentRepositoryInterface */
    private $shipCrewRepository;
    /** @var MockInterface&CrewRepositoryInterface */
    private $crewRepository;
    /** @var MockInterface&EntityManagerInterface */
    private $entityManager;

    private PlayerDeletionHandlerInterface $handler;

    #[Override]
    public function setUp(): void
    {
        $this->shipCrewRepository = $this->mock(CrewAssignmentRepositoryInterface::class);
        $this->crewRepository = $this->mock(CrewRepositoryInterface::class);
        $this->entityManager = $this->mock(EntityManagerInterface::class);

        $this->handler = new CrewDeletionHandler(
            $this->shipCrewRepository,
            $this->crewRepository,
            $this->entityManager
        );
    }

    public function testDeleteDeletesCrewAssignmentsAndCrew(): void
    {
        $user = Mockery::mock(UserInterface::class);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(42);

        $this->shipCrewRepository->shouldReceive('truncateByUser')
            ->with($user)
            ->once();

        $this->crewRepository->shouldReceive('truncateByUser')
            ->with(42)
            ->once();

        $this->entityManager->shouldReceive('flush')
            ->withNoArgs()
            ->once();

        $this->handler->delete($user);
    }
}
