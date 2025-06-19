<?php

declare(strict_types=1);

namespace Stu\Module\Building\Action;

use Mockery;
use Mockery\MockInterface;
use Override;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\StuTestCase;

class ShipyardTest extends StuTestCase
{
    private MockInterface&ColonyShipQueueRepositoryInterface $colonyShipQueueRepository;

    private Shipyard $shipyard;

    #[Override]
    public function setUp(): void
    {
        $this->colonyShipQueueRepository = Mockery::mock(ColonyShipQueueRepositoryInterface::class);

        $this->shipyard = new Shipyard(
            $this->colonyShipQueueRepository
        );
    }

    public function testDestructTruncatesQueue(): void
    {
        $colony = $this->mock(ColonyInterface::class);
        $buildingFunction = BuildingFunctionEnum::SHIELD_BATTERY;

        $this->colonyShipQueueRepository->shouldReceive('truncateByColonyAndBuildingFunction')
            ->with($colony, $buildingFunction)
            ->once();

        $this->shipyard->destruct($buildingFunction, $colony);
    }

    public function testDeactivateStopsBuildProcesses(): void
    {
        $colony = Mockery::mock(ColonyInterface::class);
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

    public function testActivateRestartsBuildProcesses(): void
    {
        $colony = Mockery::mock(ColonyInterface::class);
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
}
