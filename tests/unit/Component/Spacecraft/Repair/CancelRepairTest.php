<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\Repair;

use Mockery\MockInterface;
use Override;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;
use Stu\Orm\Repository\RepairTaskRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\StationShipRepairRepositoryInterface;
use Stu\StuTestCase;

class CancelRepairTest extends StuTestCase
{
    /** @var SpacecraftRepositoryInterface&MockInterface*/
    private $spacecraftRepo;
    /** @var RepairTaskRepositoryInterface&MockInterface */
    private $repairTaskRepo;
    /** @var ColonyShipRepairRepositoryInterface&MockInterface */
    private $colonyShipRepairRepo;
    /** @var StationShipRepairRepositoryInterface&MockInterface */
    private $stationShipRepairRepo;

    /** @var ShipInterface&MockInterface */
    private  $ship;

    private CancelRepairInterface $cancelRepair;

    #[Override]
    public function setUp(): void
    {
        $this->spacecraftRepo = $this->mock(SpacecraftRepositoryInterface::class);
        $this->repairTaskRepo = $this->mock(RepairTaskRepositoryInterface::class);
        $this->colonyShipRepairRepo = $this->mock(ColonyShipRepairRepositoryInterface::class);
        $this->stationShipRepairRepo = $this->mock(StationShipRepairRepositoryInterface::class);

        $this->ship = $this->mock(ShipInterface::class);

        $this->cancelRepair = new CancelRepair(
            $this->spacecraftRepo,
            $this->repairTaskRepo,
            $this->colonyShipRepairRepo,
            $this->stationShipRepairRepo
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
            ->twice()
            ->andReturn(42);
        $this->ship->shouldReceive('getCondition->setState')
            ->with(SpacecraftStateEnum::NONE)
            ->once();
        $this->spacecraftRepo->shouldReceive('save')
            ->with($this->ship)
            ->once();
        $this->colonyShipRepairRepo->shouldReceive('truncateByShipId')
            ->with(42)
            ->once();
        $this->stationShipRepairRepo->shouldReceive('truncateByShipId')
            ->with(42)
            ->once();

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
        $this->spacecraftRepo->shouldReceive('save')
            ->with($this->ship)
            ->once();
        $this->repairTaskRepo->shouldReceive('truncateByShipId')
            ->with(42)
            ->once();

        $result = $this->cancelRepair->cancelRepair($this->ship);

        $this->assertTrue($result);
    }
}
