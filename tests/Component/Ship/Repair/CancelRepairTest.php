<?php

declare(strict_types=1);

namespace Stu\Component\Ship\Repair;

use Override;
use Mockery\MockInterface;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;
use Stu\Orm\Repository\RepairTaskRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\StationShipRepairRepositoryInterface;
use Stu\StuTestCase;

class CancelRepairTest extends StuTestCase
{
    /**
     * @var ShipRepositoryInterface|MockInterface|null
     */
    private $shipRepo;
    /**
     * @var RepairTaskRepositoryInterface|MockInterface|null
     */
    private $repairTaskRepo;
    /**
     * @var ColonyShipRepairRepositoryInterface|MockInterface|null
     */
    private $colonyShipRepairRepo;
    /**
     * @var StationShipRepairRepositoryInterface|MockInterface|null
     */
    private $stationShipRepairRepo;

    private CancelRepairInterface $cancelRepair;
    private ShipInterface $ship;

    #[Override]
    public function setUp(): void
    {
        $this->shipRepo = $this->mock(ShipRepositoryInterface::class);
        $this->repairTaskRepo = $this->mock(RepairTaskRepositoryInterface::class);
        $this->colonyShipRepairRepo = $this->mock(ColonyShipRepairRepositoryInterface::class);
        $this->stationShipRepairRepo = $this->mock(StationShipRepairRepositoryInterface::class);

        $this->ship = $this->mock(ShipInterface::class);

        $this->cancelRepair = new CancelRepair(
            $this->shipRepo,
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
            ->andReturn(ShipStateEnum::SHIP_STATE_NONE);

        $result = $this->cancelRepair->cancelRepair($this->ship);

        $this->assertFalse($result);
    }

    public function testCancelRepairExpectTrueIfUnderPassiveRepair(): void
    {
        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(ShipStateEnum::SHIP_STATE_REPAIR_PASSIVE);
        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->twice()
            ->andReturn(42);
        $this->ship->shouldReceive('setState')
            ->with(ShipStateEnum::SHIP_STATE_NONE)
            ->once();
        $this->shipRepo->shouldReceive('save')
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
            ->andReturn(ShipStateEnum::SHIP_STATE_REPAIR_ACTIVE);
        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $this->ship->shouldReceive('setState')
            ->with(ShipStateEnum::SHIP_STATE_NONE)
            ->once();
        $this->shipRepo->shouldReceive('save')
            ->with($this->ship)
            ->once();
        $this->repairTaskRepo->shouldReceive('truncateByShipId')
            ->with(42)
            ->once();

        $result = $this->cancelRepair->cancelRepair($this->ship);

        $this->assertTrue($result);
    }
}
