<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\MockInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Entity\ColonyChangeable;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyTerraforming;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ColonySandboxRepositoryInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\ColonyTerraformingRepositoryInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\CrewTrainingRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class ColonyResetterTest extends StuTestCase
{
    /**
     * @var MockInterface&null|ColonyRepositoryInterface
     */
    private $colonyRepository;

    /**
     * @var MockInterface&null|UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var MockInterface&null|StorageRepositoryInterface
     */
    private $storageRepository;

    /**
     * @var MockInterface&null|ColonyTerraformingRepositoryInterface
     */
    private $colonyTerraformingRepository;

    /**
     * @var MockInterface&null|ColonyShipQueueRepositoryInterface
     */
    private $colonyShipQueueRepository;

    /**
     * @var null|MockInterface|PlanetFieldRepositoryInterface
     */
    private $planetFieldRepository;

    /**
     * @var null|MockInterface|CrewRepositoryInterface
     */
    private $crewRepository;

    /**
     * @var null|MockInterface|CrewTrainingRepositoryInterface
     */
    private $crewTrainingRepository;

    /**
     * @var null|MockInterface|CrewAssignmentRepositoryInterface
     */
    private $shipCrewRepository;

    /**
     * @var null|MockInterface|ColonySandboxRepositoryInterface
     */
    private $colonySandboxRepository;

    /**
     * @var null|MockInterface|PrivateMessageSenderInterface
     */
    private $privateMessageSender;

    private ColonyResetterInterface $resetter;

    #[\Override]
    public function setUp(): void
    {
        $this->colonyRepository = Mockery::mock(ColonyRepositoryInterface::class);
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->storageRepository = Mockery::mock(StorageRepositoryInterface::class);
        $this->colonyTerraformingRepository = Mockery::mock(ColonyTerraformingRepositoryInterface::class);
        $this->colonyShipQueueRepository = Mockery::mock(ColonyShipQueueRepositoryInterface::class);
        $this->planetFieldRepository = $this->mock(PlanetFieldRepositoryInterface::class);
        $this->crewRepository = $this->mock(CrewRepositoryInterface::class);
        $this->crewTrainingRepository = $this->mock(CrewTrainingRepositoryInterface::class);
        $this->shipCrewRepository = $this->mock(CrewAssignmentRepositoryInterface::class);
        $this->colonySandboxRepository = $this->mock(ColonySandboxRepositoryInterface::class);
        $this->privateMessageSender = $this->mock(PrivateMessageSenderInterface::class);

        $this->resetter = new ColonyResetter(
            $this->colonyRepository,
            $this->userRepository,
            $this->storageRepository,
            $this->colonyTerraformingRepository,
            $this->colonyShipQueueRepository,
            $this->planetFieldRepository,
            $this->crewRepository,
            $this->crewTrainingRepository,
            $this->shipCrewRepository,
            $this->colonySandboxRepository,
            $this->privateMessageSender
        );
    }

    public function testResetResetsColony(): void
    {
        $colony = Mockery::mock(Colony::class);
        $changeable = $this->mock(ColonyChangeable::class);
        $user = Mockery::mock(User::class);
        $fieldTerraforming = Mockery::mock(ColonyTerraforming::class);

        $this->userRepository->shouldReceive('getFallbackUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        //BLOCKERS
        $blockerFleet = Mockery::mock(Fleet::class);

        $blockerFleet->shouldReceive('setBlockedColony')
            ->with(null)
            ->once();

        $blockerFleetCollection = new ArrayCollection([$blockerFleet]);
        $colony->shouldReceive('getBlockers')
            ->withNoArgs()
            ->twice()
            ->andReturn($blockerFleetCollection);

        //DEFENDERS
        $defenderFleet = Mockery::mock(Fleet::class);

        $defenderFleet->shouldReceive('setDefendedColony')
            ->with(null)
            ->once();

        $defenderFleetCollection = new ArrayCollection([$defenderFleet]);
        $colony->shouldReceive('getDefenders')
            ->withNoArgs()
            ->twice()
            ->andReturn($defenderFleetCollection);

        //CREW
        $crewAssignment = $this->mock(CrewAssignment::class);
        $crew = $this->mock(Crew::class);
        $crewAssignmentsCollection = new ArrayCollection([$crewAssignment]);
        $colony->shouldReceive('getCrewAssignments')
            ->withNoArgs()
            ->once()
            ->andReturn($crewAssignmentsCollection);
        $crewAssignment->shouldReceive('getCrew')
            ->withNoArgs()
            ->once()
            ->andReturn($crew);
        $crewAssignment->shouldReceive('clearAssignment')
            ->withNoArgs()
            ->once();
        $this->shipCrewRepository->shouldReceive('delete')
            ->with($crewAssignment)
            ->once();
        $this->crewRepository->shouldReceive('delete')
            ->with($crew)
            ->once();
        $this->crewTrainingRepository->shouldReceive('truncateByColony')
            ->with($colony)
            ->once();

        //OTHER
        $colony->shouldReceive('getChangeable')
            ->withNoArgs()
            ->once()
            ->andReturn($changeable);
        $changeable->shouldReceive('setEps')
            ->with(0)
            ->once()
            ->andReturnSelf();
        $changeable->shouldReceive('setMaxEps')
            ->with(0)
            ->once()
            ->andReturnSelf();
        $changeable->shouldReceive('setMaxStorage')
            ->with(0)
            ->once()
            ->andReturnSelf();
        $changeable->shouldReceive('setWorkers')
            ->with(0)
            ->once()
            ->andReturnSelf();
        $changeable->shouldReceive('setWorkless')
            ->with(0)
            ->once()
            ->andReturnSelf();
        $changeable->shouldReceive('setMaxBev')
            ->with(0)
            ->once()
            ->andReturnSelf();
        $changeable->shouldReceive('setImmigrationstate')
            ->with(true)
            ->once()
            ->andReturnSelf();
        $changeable->shouldReceive('setPopulationlimit')
            ->with(0)
            ->once()
            ->andReturnSelf();
        $colony->shouldReceive('setUser')
            ->with($user)
            ->once()
            ->andReturnSelf();
        $colony->shouldReceive('setName')
            ->with('')
            ->once()
            ->andReturnSelf();
        $changeable->shouldReceive('setTorpedo')
            ->with(null)
            ->once()
            ->andReturnSelf();
        $changeable->shouldReceive('setShieldFrequency')
            ->with(0)
            ->once()
            ->andReturnSelf();
        $changeable->shouldReceive('setShields')
            ->with(0)
            ->once()
            ->andReturnSelf();

        $this->colonyRepository->shouldReceive('save')
            ->with($colony)
            ->once();

        $this->storageRepository->shouldReceive('truncateByColony')
            ->with($colony)
            ->once();

        $this->colonyTerraformingRepository->shouldReceive('getByColony')
            ->with([$colony])
            ->once()
            ->andReturn([$fieldTerraforming]);
        $this->colonyTerraformingRepository->shouldReceive('delete')
            ->with($fieldTerraforming)
            ->once();

        $this->colonyShipQueueRepository->shouldReceive('truncateByColony')
            ->with($colony)
            ->once();

        $this->planetFieldRepository->shouldReceive('truncateByColony')
            ->with($colony)
            ->once();

        $this->colonySandboxRepository->shouldReceive('truncateByColony')
            ->with($colony)
            ->once();

        $this->resetter->reset($colony, false);

        $this->assertTrue($blockerFleetCollection->isEmpty());
        $this->assertTrue($defenderFleetCollection->isEmpty());
    }
}