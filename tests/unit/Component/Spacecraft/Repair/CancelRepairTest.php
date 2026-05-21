<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\Repair;

use Mockery\MockInterface;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyShipRepair;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\RepairTask;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\RepairTaskRepositoryInterface;
use Stu\Orm\Repository\StationShipRepairRepositoryInterface;
use Stu\StuTestCase;

class CancelRepairTest extends StuTestCase
{
    private RepairTaskRepositoryInterface&MockInterface $repairTaskRepo;
    private ColonyShipRepairRepositoryInterface&MockInterface $colonyShipRepairRepo;
    private StationShipRepairRepositoryInterface&MockInterface $stationShipRepairRepo;
    private ColonyFunctionManagerInterface&MockInterface $colonyFunctionManager;
    private PlanetFieldRepositoryInterface&MockInterface $planetFieldRepository;
    private StorageManagerInterface&MockInterface $storageManager;
    private CommodityRepositoryInterface&MockInterface $commodityRepository;

    private Ship&MockInterface $ship;

    private CancelRepairInterface $cancelRepair;

    #[\Override]
    public function setUp(): void
    {
        $this->repairTaskRepo = $this->mock(RepairTaskRepositoryInterface::class);
        $this->colonyShipRepairRepo = $this->mock(ColonyShipRepairRepositoryInterface::class);
        $this->stationShipRepairRepo = $this->mock(StationShipRepairRepositoryInterface::class);
        $this->colonyFunctionManager = $this->mock(ColonyFunctionManagerInterface::class);
        $this->planetFieldRepository = $this->mock(PlanetFieldRepositoryInterface::class);
        $this->storageManager = $this->mock(StorageManagerInterface::class);
        $this->commodityRepository = $this->mock(CommodityRepositoryInterface::class);

        $this->ship = $this->mock(Ship::class);

        $this->cancelRepair = new CancelRepair(
            $this->repairTaskRepo,
            $this->colonyShipRepairRepo,
            $this->stationShipRepairRepo,
            $this->colonyFunctionManager,
            $this->planetFieldRepository,
            $this->storageManager,
            $this->commodityRepository
        );
    }

    public function testCancelRepairExpectFalseIfNotUnderRepair(): void
    {
        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftStateEnum::NONE);

        $result = $this->cancelRepair->cancelRepair($this->ship);

        $this->assertFalse($result);
    }

    public function testCancelRepairExpectTrueIfUnderPassiveRepair(): void
    {
        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftStateEnum::REPAIR_PASSIVE);
        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->times(2)
            ->andReturn(42);
        $this->ship->shouldReceive('getCondition->setState')
            ->with(SpacecraftStateEnum::NONE)
            ->once();
        $this->colonyShipRepairRepo->shouldReceive('getByShip')
            ->with(42)
            ->once()
            ->andReturnNull();
        $this->stationShipRepairRepo->shouldReceive('getByShip')
            ->with(42)
            ->once()
            ->andReturnNull();
        $this->colonyShipRepairRepo->shouldNotReceive('delete');
        $this->stationShipRepairRepo->shouldNotReceive('delete');

        $result = $this->cancelRepair->cancelRepair($this->ship);

        $this->assertTrue($result);
    }

    public function testCancelRepairPromotesNextColonyQueueJob(): void
    {
        $colony = $this->mock(Colony::class);
        $repairJob = $this->mock(ColonyShipRepair::class);
        $nextJob = $this->mock(ColonyShipRepair::class);
        $nextShip = $this->mock(Ship::class);
        $field = $this->mock(PlanetField::class);

        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftStateEnum::REPAIR_PASSIVE);
        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->times(2)
            ->andReturn(42);
        $this->ship->shouldReceive('getCondition->setState')
            ->with(SpacecraftStateEnum::NONE)
            ->once();

        $this->colonyShipRepairRepo->shouldReceive('getByShip')
            ->with(42)
            ->once()
            ->andReturn($repairJob);
        $this->stationShipRepairRepo->shouldReceive('getByShip')
            ->with(42)
            ->once()
            ->andReturnNull();
        $this->colonyShipRepairRepo->shouldReceive('delete')
            ->with($repairJob)
            ->once();
        $this->stationShipRepairRepo->shouldNotReceive('delete');

        $repairJob->shouldReceive('getColony')
            ->withNoArgs()
            ->once()
            ->andReturn($colony);
        $repairJob->shouldReceive('getFieldId')
            ->withNoArgs()
            ->once()
            ->andReturn(13);

        $colony->shouldReceive('getId')
            ->withNoArgs()
            ->times(2)
            ->andReturn(5);
        $colony->shouldReceive('isBlocked')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $this->colonyShipRepairRepo->shouldReceive('getByColonyField')
            ->with(5, 13)
            ->once()
            ->andReturn([$nextJob]);

        $this->planetFieldRepository->shouldReceive('getByColonyAndFieldIndex')
            ->with(5, 13)
            ->once()
            ->andReturn($field);
        $field->shouldReceive('isActive')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $this->colonyFunctionManager->shouldReceive('hasActiveFunction')
            ->once()
            ->andReturnFalse();

        $nextJob->shouldReceive('isStopped')
            ->withNoArgs()
            ->twice()
            ->andReturnFalse();
        $nextJob->shouldReceive('getStopDate')
            ->withNoArgs()
            ->twice()
            ->andReturn(0);
        $nextJob->shouldReceive('getFinishTime')
            ->withNoArgs()
            ->once()
            ->andReturn(0);
        $nextJob->shouldReceive('getShip')
            ->withNoArgs()
            ->once()
            ->andReturn($nextShip);
        $nextJob->shouldReceive('setFinishTime')
            ->with(\Mockery::type('int'))
            ->once();

        $nextShip->shouldReceive('getRump->getBuildtime')
            ->once()
            ->andReturn(120);

        $result = $this->cancelRepair->cancelRepair($this->ship);

        $this->assertTrue($result);
    }

    public function testCancelRepairExpectTrueIfUnderActiveRepair(): void
    {
        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftStateEnum::REPAIR_ACTIVE);
        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $this->ship->shouldReceive('getCondition->setState')
            ->with(SpacecraftStateEnum::NONE)
            ->once();
        $this->repairTaskRepo->shouldReceive('truncateByShipId')
            ->with(42)
            ->once();
        $this->repairTaskRepo->shouldReceive('getByShip')
            ->with(42)
            ->once()
            ->andReturnNull();
        $this->storageManager->shouldNotReceive('upperStorage');

        $result = $this->cancelRepair->cancelRepair($this->ship);

        $this->assertTrue($result);
    }

    public function testCancelRepairWithResultExpectRefundIfUnderActiveRepair(): void
    {
        $repairTask = $this->mock(RepairTask::class);
        $sparePart = $this->mock(Commodity::class);
        $systemComponent = $this->mock(Commodity::class);

        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftStateEnum::REPAIR_ACTIVE);
        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $this->ship->shouldReceive('getMaxHull')
            ->withNoArgs()
            ->once()
            ->andReturn(450);
        $this->ship->shouldReceive('getCondition->setState')
            ->with(SpacecraftStateEnum::NONE)
            ->once();

        $this->repairTaskRepo->shouldReceive('getByShip')
            ->with(42)
            ->once()
            ->andReturn($repairTask);
        $repairTask->shouldReceive('getHealingPercentage')
            ->withNoArgs()
            ->once()
            ->andReturn(RepairTaskConstants::BOTH_MIN);

        $this->commodityRepository->shouldReceive('find')
            ->with(CommodityTypeConstants::COMMODITY_SPARE_PART)
            ->once()
            ->andReturn($sparePart);
        $this->commodityRepository->shouldReceive('find')
            ->with(CommodityTypeConstants::COMMODITY_SYSTEM_COMPONENT)
            ->once()
            ->andReturn($systemComponent);
        $this->storageManager->shouldReceive('upperStorage')
            ->with($this->ship, $sparePart, 3)
            ->once();
        $this->storageManager->shouldReceive('upperStorage')
            ->with($this->ship, $systemComponent, 3)
            ->once();
        $this->repairTaskRepo->shouldReceive('truncateByShipId')
            ->with(42)
            ->once();

        $result = $this->cancelRepair->cancelRepairWithResult($this->ship);

        $this->assertTrue($result->isCancelled());
        $this->assertSame(3, $result->getRefundedSpareParts());
        $this->assertSame(3, $result->getRefundedSystemComponents());
    }
}
