<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\Repair;

use Mockery\MockInterface;
use Override;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ColonyShipRepairInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftSystemInterface;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;
use Stu\Orm\Repository\RepairTaskRepositoryInterface;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;
use Stu\StuTestCase;

class RepairUtilTest extends StuTestCase
{
    /** @var SpacecraftSystemRepositoryInterface|MockInterface */
    private $shipSystemRepository;

    /** @var RepairTaskRepositoryInterface|MockInterface */
    private $repairTaskRepository;

    /** @var ColonyShipRepairRepositoryInterface|MockInterface */
    private $colonyShipRepairRepository;

    /** @var StorageManagerInterface|MockInterface */
    private $storageManager;

    /** @var ColonyFunctionManagerInterface|MockInterface */
    private $colonyFunctionManager;

    /** @var PrivateMessageSenderInterface|MockInterface */
    private $privateMessageSender;

    /** @var ShipWrapperInterface|MockInterface */
    private $wrapper;

    /** @var ShipInterface|MockInterface */
    private $ship;

    private RepairUtilInterface $subject;

    #[Override]
    public function setUp(): void
    {
        $this->shipSystemRepository = $this->mock(SpacecraftSystemRepositoryInterface::class);
        $this->repairTaskRepository = $this->mock(RepairTaskRepositoryInterface::class);
        $this->colonyShipRepairRepository = $this->mock(ColonyShipRepairRepositoryInterface::class);
        $this->storageManager = $this->mock(StorageManagerInterface::class);
        $this->colonyFunctionManager = $this->mock(ColonyFunctionManagerInterface::class);
        $this->privateMessageSender = $this->mock(PrivateMessageSenderInterface::class);

        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->ship = $this->mock(ShipInterface::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->subject = new RepairUtil(
            $this->shipSystemRepository,
            $this->repairTaskRepository,
            $this->colonyShipRepairRepository,
            $this->storageManager,
            $this->colonyFunctionManager,
            $this->privateMessageSender
        );
    }


    public function testGetRepairDurationWithIntactShipExpectZero(): void
    {
        $this->wrapper->shouldReceive('getDamagedSystems')
            ->withNoArgs()->once()->andReturn([]);

        $this->ship->shouldReceive('getMaxHull')
            ->withNoArgs()->once()->andReturn(100);
        $this->ship->shouldReceive('getCondition->getHull')
            ->withNoArgs()->once()->andReturn(100);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()->once()->andReturn(42);

        $this->colonyShipRepairRepository->shouldReceive('getByShip')
            ->with(42)->once()->andReturn(null);

        $duration = $this->subject->getRepairDuration($this->wrapper);

        $this->assertEquals(0, $duration);
    }

    public function testGetRepairDurationWithDamagedHull(): void
    {
        $this->wrapper->shouldReceive('getDamagedSystems')
            ->withNoArgs()->once()->andReturn([]);

        $this->ship->shouldReceive('getMaxHull')
            ->withNoArgs()->once()->andReturn(1000);
        $this->ship->shouldReceive('getCondition->getHull')
            ->withNoArgs()->once()->andReturn(790);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()->once()->andReturn(42);

        $this->colonyShipRepairRepository->shouldReceive('getByShip')
            ->with(42)->once()->andReturn(null);

        $duration = $this->subject->getRepairDuration($this->wrapper);

        $this->assertEquals(3, $duration);
    }

    public function testGetRepairDurationWithDamagedSystems(): void
    {
        $damagedSystem1 = $this->mock(SpacecraftSystemInterface::class);
        $damagedSystem2 = $this->mock(SpacecraftSystemInterface::class);

        $this->wrapper->shouldReceive('getDamagedSystems')
            ->withNoArgs()->once()->andReturn([$damagedSystem1, $damagedSystem2]);

        $this->ship->shouldReceive('getMaxHull')
            ->withNoArgs()->once()->andReturn(100);
        $this->ship->shouldReceive('getCondition->getHull')
            ->withNoArgs()->once()->andReturn(100);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()->once()->andReturn(42);

        $this->colonyShipRepairRepository->shouldReceive('getByShip')
            ->with(42)->once()->andReturn(null);

        $duration = $this->subject->getRepairDuration($this->wrapper);

        $this->assertEquals(1, $duration);
    }

    public function testGetRepairDurationWithDamagedSystemsAndInactiveRepairStation(): void
    {
        $this->ship->shouldReceive('getMaxHull')
            ->withNoArgs()->once()->andReturn(1000);
        $this->ship->shouldReceive('getCondition->getHull')
            ->withNoArgs()->once()->andReturn(600);

        $this->wrapper->shouldReceive('getDamagedSystems')
            ->withNoArgs()->once()->andReturn([]);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()->once()->andReturn(42);

        $colonyShipRepair = $this->mock(ColonyShipRepairInterface::class);
        $colony = $this->mock(ColonyInterface::class);

        $this->colonyShipRepairRepository->shouldReceive('getByShip')
            ->with(42)->once()->andReturn($colonyShipRepair);

        $colonyShipRepair->shouldReceive('getColony')
            ->withNoArgs()
            ->once()
            ->andReturn($colony);

        $this->colonyFunctionManager->shouldReceive('hasActiveFunction')
            ->with($colony, BuildingFunctionEnum::REPAIR_SHIPYARD)
            ->once()
            ->andReturnFalse();

        $duration = $this->subject->getRepairDuration($this->wrapper);

        $this->assertEquals(4, $duration);
    }

    public function testGetRepairDurationWithDamagedSystemsAndActiveRepairStation(): void
    {
        $damagedSystem1 = $this->mock(SpacecraftSystemInterface::class);
        $damagedSystem2 = $this->mock(SpacecraftSystemInterface::class);
        $damagedSystem3 = $this->mock(SpacecraftSystemInterface::class);
        $damagedSystem4 = $this->mock(SpacecraftSystemInterface::class);

        $this->wrapper->shouldReceive('getDamagedSystems')
            ->withNoArgs()->once()->andReturn([
                $damagedSystem1,
                $damagedSystem2,
                $damagedSystem3,
                $damagedSystem4
            ]);

        $this->ship->shouldReceive('getMaxHull')
            ->withNoArgs()->once()->andReturn(1000);
        $this->ship->shouldReceive('getCondition->getHull')
            ->withNoArgs()->once()->andReturn(800);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()->once()->andReturn(42);

        $colonyShipRepair = $this->mock(ColonyShipRepairInterface::class);
        $colony = $this->mock(ColonyInterface::class);

        $this->colonyShipRepairRepository->shouldReceive('getByShip')
            ->with(42)->once()->andReturn($colonyShipRepair);
        $colonyShipRepair->shouldReceive('getColony')
            ->withNoArgs()
            ->once()
            ->andReturn($colony);

        $this->colonyFunctionManager->shouldReceive('hasActiveFunction')
            ->with($colony, BuildingFunctionEnum::REPAIR_SHIPYARD)
            ->once()
            ->andReturnTrue();

        $duration = $this->subject->getRepairDuration($this->wrapper);

        $this->assertEquals(1, $duration);
    }

    public function testGetRepairDurationPreviewWithDamagedHullAndNotOverColony(): void
    {
        $this->ship->shouldReceive('getMaxHull')
            ->withNoArgs()->once()->andReturn(1000);
        $this->ship->shouldReceive('getCondition->getHull')
            ->withNoArgs()->once()->andReturn(600);

        $this->wrapper->shouldReceive('getDamagedSystems')
            ->withNoArgs()->once()->andReturn([]);

        $this->ship->shouldReceive('isOverColony')
            ->withNoArgs()->once()->andReturn(null);

        $duration = $this->subject->getRepairDurationPreview($this->wrapper);

        $this->assertEquals(4, $duration);
    }

    public function testGetRepairDurationPreviewWithDamagedHullAndOverColonyWithInactiveRepairStation(): void
    {
        $this->ship->shouldReceive('getMaxHull')
            ->withNoArgs()->once()->andReturn(1000);
        $this->ship->shouldReceive('getCondition->getHull')
            ->withNoArgs()->once()->andReturn(600);

        $this->wrapper->shouldReceive('getDamagedSystems')
            ->withNoArgs()->once()->andReturn([]);

        $colony = $this->mock(ColonyInterface::class);

        $this->colonyFunctionManager->shouldReceive('hasActiveFunction')
            ->with($colony, BuildingFunctionEnum::REPAIR_SHIPYARD)
            ->once()
            ->andReturnFalse();

        $this->ship->shouldReceive('isOverColony')
            ->withNoArgs()->once()->andReturn($colony);

        $duration = $this->subject->getRepairDurationPreview($this->wrapper);

        $this->assertEquals(4, $duration);
    }

    public function testGetRepairDurationPreviewWithDamagedHullAndOverColonyWithActiveRepairStation(): void
    {
        $this->ship->shouldReceive('getMaxHull')
            ->withNoArgs()->once()->andReturn(1000);
        $this->ship->shouldReceive('getCondition->getHull')
            ->withNoArgs()->once()->andReturn(500);

        $this->wrapper->shouldReceive('getDamagedSystems')
            ->withNoArgs()->once()->andReturn([]);

        $colony = $this->mock(ColonyInterface::class);

        $this->colonyFunctionManager->shouldReceive('hasActiveFunction')
            ->with($colony, BuildingFunctionEnum::REPAIR_SHIPYARD)
            ->once()
            ->andReturnTrue();

        $this->ship->shouldReceive('isOverColony')
            ->withNoArgs()->once()->andReturn($colony);

        $duration = $this->subject->getRepairDurationPreview($this->wrapper);

        $this->assertEquals(3, $duration);
    }
}
