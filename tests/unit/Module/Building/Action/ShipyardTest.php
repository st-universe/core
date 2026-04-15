<?php

declare(strict_types=1);

namespace Stu\Module\Building\Action;

use Mockery;
use Mockery\MockInterface;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Spacecraft\Repair\RepairUtilInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyShipRepair;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\StuTestCase;

class ShipyardTest extends StuTestCase
{
    private MockInterface&ColonyShipQueueRepositoryInterface $colonyShipQueueRepository;
    private MockInterface&ColonyShipRepairRepositoryInterface $colonyShipRepairRepository;
    private MockInterface&ColonyFunctionManagerInterface $colonyFunctionManager;
    private MockInterface&RepairUtilInterface $repairUtil;
    private MockInterface&PlanetFieldRepositoryInterface $planetFieldRepository;

    private Shipyard $shipyard;

    #[\Override]
    public function setUp(): void
    {
        $this->colonyShipQueueRepository = Mockery::mock(ColonyShipQueueRepositoryInterface::class);
        $this->colonyShipRepairRepository = Mockery::mock(ColonyShipRepairRepositoryInterface::class);
        $this->colonyFunctionManager = Mockery::mock(ColonyFunctionManagerInterface::class);
        $this->repairUtil = Mockery::mock(RepairUtilInterface::class);
        $this->planetFieldRepository = Mockery::mock(PlanetFieldRepositoryInterface::class);

        $this->shipyard = new Shipyard(
            $this->colonyShipQueueRepository,
            $this->colonyShipRepairRepository,
            $this->colonyFunctionManager,
            $this->repairUtil,
            $this->planetFieldRepository
        );
    }

    public function testDestructTruncatesQueue(): void
    {
        $colony = $this->mock(Colony::class);
        $buildingFunction = BuildingFunctionEnum::SHIELD_BATTERY;

        $this->colonyShipQueueRepository->shouldReceive('truncateByColonyAndBuildingFunction')
            ->with($colony, $buildingFunction)
            ->once();

        $this->shipyard->destruct($buildingFunction, $colony);
    }

    public function testDeactivateStopsBuildProcesses(): void
    {
        $colony = Mockery::mock(Colony::class);
        $buildingFunction = BuildingFunctionEnum::SHIELD_BATTERY;

        $colony->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(666);

        $this->colonyShipQueueRepository->shouldReceive('stopQueueByColonyAndBuildingFunction')
            ->with(666, $buildingFunction)
            ->once();

        $this->shipyard->deactivate($buildingFunction, $colony);
    }

    public function testDeactivateStopsRepairProcessesOnField(): void
    {
        $colony = Mockery::mock(Colony::class);
        $field = Mockery::mock(PlanetField::class);
        $repair = Mockery::mock(ColonyShipRepair::class);
        $buildingFunction = BuildingFunctionEnum::SHIELD_BATTERY;

        $colony->shouldReceive('getId')
            ->withNoArgs()
            ->times(2)
            ->andReturn(666);
        $field->shouldReceive('getFieldId')
            ->withNoArgs()
            ->once()
            ->andReturn(12);

        $this->colonyShipQueueRepository->shouldReceive('stopQueueByColonyAndBuildingFunction')
            ->with(666, $buildingFunction)
            ->once();

        $this->colonyShipRepairRepository->shouldReceive('getByColonyField')
            ->with(666, 12)
            ->once()
            ->andReturn([$repair]);

        $repair->shouldReceive('getFinishTime')
            ->withNoArgs()
            ->once()
            ->andReturn(1234);
        $repair->shouldReceive('getStopDate')
            ->withNoArgs()
            ->once()
            ->andReturn(0);
        $repair->shouldReceive('isStopped')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $repair->shouldReceive('setStopDate')
            ->with(Mockery::type('int'))
            ->once();

        $this->shipyard->deactivate($buildingFunction, $colony, $field);
    }

    public function testActivateRestartsBuildProcesses(): void
    {
        $colony = Mockery::mock(Colony::class);
        $buildingFunction = BuildingFunctionEnum::SHIELD_BATTERY;

        $colony->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(666);

        $this->colonyShipQueueRepository->shouldReceive('restartQueueByColonyAndBuildingFunction')
            ->with(666, $buildingFunction)
            ->once();

        $this->shipyard->activate($buildingFunction, $colony);
    }

    public function testActivateRestartsRepairProcessesOnField(): void
    {
        $colony = Mockery::mock(Colony::class);
        $field = Mockery::mock(PlanetField::class);
        $repair = Mockery::mock(ColonyShipRepair::class);
        $ship = $this->mock(\Stu\Orm\Entity\Ship::class);
        $buildingFunction = BuildingFunctionEnum::SHIELD_BATTERY;

        $colony->shouldReceive('getId')
            ->withNoArgs()
            ->times(2)
            ->andReturn(666);
        $field->shouldReceive('getFieldId')
            ->withNoArgs()
            ->once()
            ->andReturn(12);

        $this->colonyShipQueueRepository->shouldReceive('restartQueueByColonyAndBuildingFunction')
            ->with(666, $buildingFunction)
            ->once();

        $this->colonyShipRepairRepository->shouldReceive('getByColonyField')
            ->with(666, 12)
            ->once()
            ->andReturn([$repair]);

        $this->colonyFunctionManager->shouldReceive('hasActiveFunction')
            ->with($colony, BuildingFunctionEnum::REPAIR_SHIPYARD)
            ->once()
            ->andReturnFalse();

        $repair->shouldReceive('isStopped')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $repair->shouldReceive('getStopDate')
            ->withNoArgs()
            ->twice()
            ->andReturn(0);
        $repair->shouldReceive('getFinishTime')
            ->withNoArgs()
            ->once()
            ->andReturn(0);
        $repair->shouldReceive('getShip')
            ->withNoArgs()
            ->once()
            ->andReturn($ship);

        $this->repairUtil->shouldReceive('getPassiveRepairStepDuration')
            ->with($ship)
            ->once()
            ->andReturn(60);

        $repair->shouldReceive('setFinishTime')
            ->with(Mockery::on(static fn (int $value): bool => $value >= time() + 50 && $value <= time() + 70))
            ->once();

        $this->shipyard->activate($buildingFunction, $colony, $field);
    }

    public function testActivateRepairStationStartsSecondQueueSlotImmediately(): void
    {
        $colony = Mockery::mock(Colony::class);
        $triggerField = Mockery::mock(PlanetField::class);
        $queueField = Mockery::mock(PlanetField::class);
        $jobOne = Mockery::mock(ColonyShipRepair::class);
        $jobTwo = Mockery::mock(ColonyShipRepair::class);
        $shipOne = $this->mock(\Stu\Orm\Entity\Ship::class);
        $shipTwo = $this->mock(\Stu\Orm\Entity\Ship::class);

        $colony->shouldReceive('getId')
            ->andReturn(666);

        $this->colonyShipQueueRepository->shouldReceive('restartQueueByColonyAndBuildingFunction')
            ->with(666, BuildingFunctionEnum::REPAIR_SHIPYARD)
            ->once();

        foreach ([$jobOne, $jobTwo] as $job) {
            $job->shouldReceive('getColonyId')
                ->once()
                ->andReturn(666);
            $job->shouldReceive('getFieldId')
                ->once()
                ->andReturn(13);
            $job->shouldReceive('getFinishTime')
                ->twice()
                ->andReturn(0);
            $job->shouldReceive('getStopDate')
                ->twice()
                ->andReturn(0);
            $job->shouldReceive('isStopped')
                ->once()
                ->andReturnFalse();
        }

        $jobOne->shouldReceive('getShip')
            ->once()
            ->andReturn($shipOne);
        $jobTwo->shouldReceive('getShip')
            ->once()
            ->andReturn($shipTwo);

        $jobOne->shouldReceive('setFinishTime')
            ->with(Mockery::type('int'))
            ->once();
        $jobTwo->shouldReceive('setFinishTime')
            ->with(Mockery::type('int'))
            ->once();

        $this->colonyShipRepairRepository->shouldReceive('getAllOrdered')
            ->once()
            ->andReturn([$jobOne, $jobTwo]);

        $this->planetFieldRepository->shouldReceive('getByColonyAndFieldIndex')
            ->with(666, 13)
            ->once()
            ->andReturn($queueField);

        $queueField->shouldReceive('isActive')
            ->once()
            ->andReturnTrue();

        $this->repairUtil->shouldReceive('getPassiveRepairStepDuration')
            ->with($shipOne)
            ->once()
            ->andReturn(60);
        $this->repairUtil->shouldReceive('getPassiveRepairStepDuration')
            ->with($shipTwo)
            ->once()
            ->andReturn(60);

        $this->shipyard->activate(BuildingFunctionEnum::REPAIR_SHIPYARD, $colony, $triggerField);
    }

    public function testDeactivateRepairStationUsesFieldIndexForIgnoreFilter(): void
    {
        $colony = Mockery::mock(Colony::class);
        $triggerField = Mockery::mock(PlanetField::class);

        $colony->shouldReceive('getId')
            ->once()
            ->andReturn(666);

        $this->colonyShipQueueRepository->shouldReceive('stopQueueByColonyAndBuildingFunction')
            ->with(666, BuildingFunctionEnum::REPAIR_SHIPYARD)
            ->once();

        $triggerField->shouldReceive('getFieldId')
            ->once()
            ->andReturn(13);

        $this->colonyFunctionManager->shouldReceive('hasActiveFunction')
            ->with($colony, BuildingFunctionEnum::REPAIR_SHIPYARD, false, [13])
            ->once()
            ->andReturnFalse();

        $this->colonyShipRepairRepository->shouldReceive('getAllOrdered')
            ->once()
            ->andReturn([]);

        $this->shipyard->deactivate(BuildingFunctionEnum::REPAIR_SHIPYARD, $colony, $triggerField);
    }
}
