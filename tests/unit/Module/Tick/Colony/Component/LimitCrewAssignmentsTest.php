<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Colony\Component;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\MockInterface;
use Stu\Component\Colony\ColonyPopulationCalculatorInterface;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\StuTestCase;

class LimitCrewAssignmentsTest extends StuTestCase
{
    private MockInterface&ColonyLibFactoryInterface $colonyLibFactory;
    private MockInterface&CrewAssignmentRepositoryInterface $crewAssignmentRepository;
    private MockInterface&CrewRepositoryInterface $crewRepository;
    private MockInterface&EntityManagerInterface $entityManager;

    private MockInterface&Colony $colony;
    private MockInterface&InformationInterface $information;

    private ColonyTickComponentInterface $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->colonyLibFactory = $this->mock(ColonyLibFactoryInterface::class);
        $this->crewAssignmentRepository = $this->mock(CrewAssignmentRepositoryInterface::class);
        $this->crewRepository = $this->mock(CrewRepositoryInterface::class);
        $this->entityManager = $this->mock(EntityManagerInterface::class);

        $this->colony = $this->mock(Colony::class);
        $this->information = $this->mock(InformationInterface::class);

        $this->subject = new LimitCrewAssignments(
            $this->colonyLibFactory,
            $this->crewAssignmentRepository,
            $this->crewRepository,
            $this->entityManager
        );
    }

    public function testWorkExpectNothingWhenCrewIsWithinLimit(): void
    {
        $populationCalculator = $this->mock(ColonyPopulationCalculatorInterface::class);
        $production = [];

        $this->colonyLibFactory->shouldReceive('createColonyPopulationCalculator')
            ->with($this->colony, $production)
            ->once()
            ->andReturn($populationCalculator);

        $populationCalculator->shouldReceive('getCrewLimit')
            ->withNoArgs()
            ->once()
            ->andReturn(5);

        $this->crewAssignmentRepository->shouldReceive('getAmountByColony')
            ->with($this->colony)
            ->once()
            ->andReturn(5);

        $this->entityManager->shouldNotReceive('lock');
        $this->crewAssignmentRepository->shouldNotReceive('getByColony');

        $this->subject->work($this->colony, $production, $this->information);
    }

    public function testWorkExpectExcessCrewToQuit(): void
    {
        $user = $this->mock(User::class);
        $connection = $this->mock(Connection::class);
        $populationCalculator = $this->mock(ColonyPopulationCalculatorInterface::class);
        $crewAssignment1 = $this->mock(CrewAssignment::class);
        $crewAssignment2 = $this->mock(CrewAssignment::class);
        $crew1 = $this->mock(Crew::class);
        $crew2 = $this->mock(Crew::class);
        $production = [];

        $this->colonyLibFactory->shouldReceive('createColonyPopulationCalculator')
            ->with($this->colony, $production)
            ->twice()
            ->andReturn($populationCalculator);

        $populationCalculator->shouldReceive('getCrewLimit')
            ->withNoArgs()
            ->twice()
            ->andReturn(5);

        $this->crewAssignmentRepository->shouldReceive('getAmountByColony')
            ->with($this->colony)
            ->twice()
            ->andReturn(7);

        $this->colony->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $this->entityManager->shouldReceive('getConnection')
            ->withNoArgs()
            ->once()
            ->andReturn($connection);
        $connection->shouldReceive('isTransactionActive')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->entityManager->shouldReceive('lock')
            ->with($user, LockMode::PESSIMISTIC_WRITE)
            ->once();

        $this->crewAssignmentRepository->shouldReceive('getByColony')
            ->with($this->colony, 2)
            ->once()
            ->andReturn([$crewAssignment1, $crewAssignment2]);

        $crewAssignment1->shouldReceive('getCrew')
            ->withNoArgs()
            ->once()
            ->andReturn($crew1);
        $crewAssignment1->shouldReceive('clearAssignment')
            ->withNoArgs()
            ->once();
        $crewAssignment2->shouldReceive('getCrew')
            ->withNoArgs()
            ->once()
            ->andReturn($crew2);
        $crewAssignment2->shouldReceive('clearAssignment')
            ->withNoArgs()
            ->once();

        $this->crewAssignmentRepository->shouldReceive('delete')
            ->with($crewAssignment1)
            ->once()
            ->ordered();
        $this->crewRepository->shouldReceive('delete')
            ->with($crew1)
            ->once()
            ->ordered();
        $this->crewAssignmentRepository->shouldReceive('delete')
            ->with($crewAssignment2)
            ->once()
            ->ordered();
        $this->crewRepository->shouldReceive('delete')
            ->with($crew2)
            ->once()
            ->ordered();

        $this->information->shouldReceive('addInformationf')
            ->with(Mockery::type('string'), 2)
            ->once()
            ->andReturn($this->information);

        $this->subject->work($this->colony, $production, $this->information);
    }
}
