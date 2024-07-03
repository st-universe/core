<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\MockInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ColonyTerraformingInterface;
use Stu\Orm\Entity\CrewInterface;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\ShipCrewInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ColonySandboxRepositoryInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\ColonyTerraformingRepositoryInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\CrewTrainingRepositoryInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class ColonyResetterTest extends StuTestCase
{
    /**
     * @var MockInterface|null|ColonyRepositoryInterface
     */
    private $colonyRepository;

    /**
     * @var MockInterface|null|UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var MockInterface|null|StorageRepositoryInterface
     */
    private $storageRepository;

    /**
     * @var MockInterface|null|ColonyTerraformingRepositoryInterface
     */
    private $colonyTerraformingRepository;

    /**
     * @var MockInterface|null|ColonyShipQueueRepositoryInterface
     */
    private $colonyShipQueueRepository;

    /**
     * @var null|MockInterface|PlanetFieldRepositoryInterface
     */
    private $planetFieldRepository;

    /**
     * @var null|MockInterface|FleetRepositoryInterface
     */
    private $fleetRepository;

    /**
     * @var null|MockInterface|CrewRepositoryInterface
     */
    private $crewRepository;

    /**
     * @var null|MockInterface|CrewTrainingRepositoryInterface
     */
    private $crewTrainingRepository;

    /**
     * @var null|MockInterface|ShipCrewRepositoryInterface
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

    public function setUp(): void
    {
        $this->colonyRepository = Mockery::mock(ColonyRepositoryInterface::class);
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->storageRepository = Mockery::mock(StorageRepositoryInterface::class);
        $this->colonyTerraformingRepository = Mockery::mock(ColonyTerraformingRepositoryInterface::class);
        $this->colonyShipQueueRepository = Mockery::mock(ColonyShipQueueRepositoryInterface::class);
        $this->planetFieldRepository = $this->mock(PlanetFieldRepositoryInterface::class);
        $this->fleetRepository = $this->mock(FleetRepositoryInterface::class);
        $this->crewRepository = $this->mock(CrewRepositoryInterface::class);
        $this->crewTrainingRepository = $this->mock(CrewTrainingRepositoryInterface::class);
        $this->shipCrewRepository = $this->mock(ShipCrewRepositoryInterface::class);
        $this->colonySandboxRepository = $this->mock(ColonySandboxRepositoryInterface::class);
        $this->privateMessageSender = $this->mock(PrivateMessageSenderInterface::class);

        $this->resetter = new ColonyResetter(
            $this->colonyRepository,
            $this->userRepository,
            $this->storageRepository,
            $this->colonyTerraformingRepository,
            $this->colonyShipQueueRepository,
            $this->planetFieldRepository,
            $this->fleetRepository,
            $this->crewRepository,
            $this->crewTrainingRepository,
            $this->shipCrewRepository,
            $this->colonySandboxRepository,
            $this->privateMessageSender
        );
    }

    public function testResetResetsColony(): void
    {
        $colony = Mockery::mock(ColonyInterface::class);
        $user = Mockery::mock(UserInterface::class);
        $fieldTerraforming = Mockery::mock(ColonyTerraformingInterface::class);

        $this->userRepository->shouldReceive('getFallbackUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        //BLOCKERS
        $blockerFleet = Mockery::mock(FleetInterface::class);

        $blockerFleet->shouldReceive('setBlockedColony')
            ->with(null)
            ->once();

        $this->fleetRepository->shouldReceive('save')
            ->with($blockerFleet)
            ->once();

        $blockerFleetCollection = new ArrayCollection([$blockerFleet]);
        $colony->shouldReceive('getBlockers')
            ->withNoArgs()
            ->twice()
            ->andReturn($blockerFleetCollection);

        //DEFENDERS
        $defenderFleet = Mockery::mock(FleetInterface::class);

        $defenderFleet->shouldReceive('setDefendedColony')
            ->with(null)
            ->once();

        $this->fleetRepository->shouldReceive('save')
            ->with($defenderFleet)
            ->once();

        $defenderFleetCollection = new ArrayCollection([$defenderFleet]);
        $colony->shouldReceive('getDefenders')
            ->withNoArgs()
            ->twice()
            ->andReturn($defenderFleetCollection);

        //CREW
        $crewAssignment = $this->mock(ShipCrewInterface::class);
        $crew = $this->mock(CrewInterface::class);
        $crewAssignmentsCollection = new ArrayCollection([$crewAssignment]);
        $colony->shouldReceive('getCrewAssignments')
            ->withNoArgs()
            ->once()
            ->andReturn($crewAssignmentsCollection);
        $crewAssignment->shouldReceive('getCrew')
            ->withNoArgs()
            ->once()
            ->andReturn($crew);
        $this->crewRepository->shouldReceive('delete')
            ->with($crew)
            ->once();
        $this->shipCrewRepository->shouldReceive('delete')
            ->with($crewAssignment)
            ->once();
        $this->crewTrainingRepository->shouldReceive('truncateByColony')
            ->with($colony)
            ->once();

        //OTHER
        $colony->shouldReceive('setEps')
            ->with(0)
            ->once()
            ->andReturnSelf();
        $colony->shouldReceive('setMaxEps')
            ->with(0)
            ->once()
            ->andReturnSelf();
        $colony->shouldReceive('setMaxStorage')
            ->with(0)
            ->once()
            ->andReturnSelf();
        $colony->shouldReceive('setWorkers')
            ->with(0)
            ->once()
            ->andReturnSelf();
        $colony->shouldReceive('setWorkless')
            ->with(0)
            ->once()
            ->andReturnSelf();
        $colony->shouldReceive('setMaxBev')
            ->with(0)
            ->once()
            ->andReturnSelf();
        $colony->shouldReceive('setImmigrationstate')
            ->with(true)
            ->once()
            ->andReturnSelf();
        $colony->shouldReceive('setPopulationlimit')
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
        $colony->shouldReceive('setTorpedo')
            ->with(null)
            ->once()
            ->andReturnSelf();
        $colony->shouldReceive('setShieldFrequency')
            ->with(0)
            ->once()
            ->andReturnSelf();
        $colony->shouldReceive('setShields')
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
