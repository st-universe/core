<?php

declare(strict_types=1);

namespace Stu\Module\Building\Action;

use Override;
use Mockery;
use Mockery\MockInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\StuTestCase;

class ShipyardTest extends StuTestCase
{
    /**
     * @var null|MockInterface|ColonyShipQueueRepositoryInterface
     */
    private $colonyShipQueueRepository;

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
        $buildingFunctionId = 42;

        $this->colonyShipQueueRepository->shouldReceive('truncateByColonyAndBuildingFunction')
            ->with($colony, $buildingFunctionId)
            ->once();

        $this->shipyard->destruct($buildingFunctionId, $colony);
    }

    public function testDeactivateStopsBuildProcesses(): void
    {
        $colony = Mockery::mock(ColonyInterface::class);
        $buildingFunctionId = 42;

        $colony->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(666);

        $this->colonyShipQueueRepository->shouldReceive('stopQueueByColonyAndBuildingFunction')
            ->with(666, $buildingFunctionId)
            ->once();

        $this->shipyard->deactivate($buildingFunctionId, $colony);
    }

    public function testActivateRestartsBuildProcesses(): void
    {
        $colony = Mockery::mock(ColonyInterface::class);
        $buildingFunctionId = 42;

        $colony->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(666);

        $this->colonyShipQueueRepository->shouldReceive('restartQueueByColonyAndBuildingFunction')
            ->with(666, $buildingFunctionId)
            ->once();

        $this->shipyard->activate($buildingFunctionId, $colony);
    }
}
