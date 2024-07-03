<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Override;
use Mockery;
use Mockery\MockInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\StuTestCase;

class CrewDeletionHandlerTest extends StuTestCase
{
    /**
     * @var null|MockInterface|ShipCrewRepositoryInterface
     */
    private $shipCrewRepository;

    /**
     * @var null|MockInterface|CrewRepositoryInterface
     */
    private $crewRepository;

    private PlayerDeletionHandlerInterface $handler;

    #[Override]
    public function setUp(): void
    {
        $this->shipCrewRepository = $this->mock(ShipCrewRepositoryInterface::class);
        $this->crewRepository = $this->mock(CrewRepositoryInterface::class);

        $this->handler = new CrewDeletionHandler(
            $this->shipCrewRepository,
            $this->crewRepository
        );
    }

    public function testDeleteDeletesCrewAssignmentsAndCrew(): void
    {
        $user = Mockery::mock(UserInterface::class);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->twice()
            ->andReturn(42);

        $this->shipCrewRepository->shouldReceive('truncateByUser')
            ->with(42)
            ->once();

        $this->crewRepository->shouldReceive('truncateByUser')
            ->with(42)
            ->once();

        $this->handler->delete($user);
    }
}
