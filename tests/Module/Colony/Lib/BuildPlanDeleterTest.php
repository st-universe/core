<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Override;
use Mockery\MockInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\StuTestCase;

class BuildPlanDeleterTest extends StuTestCase
{
    /** @var MockInterface&ShipBuildplanRepositoryInterface */
    private MockInterface $shipBuildplanRepository;

    /** @var MockInterface&BuildplanModuleRepositoryInterface */
    private MockInterface $buildplanModuleRepository;

    /** @var MockInterface&ColonyShipQueueRepositoryInterface */
    private MockInterface $colonyShipQueueRepository;

    private BuildPlanDeleter $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->shipBuildplanRepository = $this->mock(ShipBuildplanRepositoryInterface::class);
        $this->buildplanModuleRepository = $this->mock(BuildplanModuleRepositoryInterface::class);
        $this->colonyShipQueueRepository = $this->mock(ColonyShipQueueRepositoryInterface::class);

        $this->subject = new BuildPlanDeleter(
            $this->shipBuildplanRepository,
            $this->buildplanModuleRepository,
            $this->colonyShipQueueRepository
        );
    }

    public function testDeleteDeletes(): void
    {
        $shipBuildPlan = $this->mock(ShipBuildplanInterface::class);

        $planId = 666;

        $shipBuildPlan->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($planId);

        $this->buildplanModuleRepository->shouldReceive('truncateByBuildplan')
            ->with($planId)
            ->once();

        $this->shipBuildplanRepository->shouldReceive('delete')
            ->with($shipBuildPlan)
            ->once();

        $this->subject->delete($shipBuildPlan);
    }

    public function testIsDeletableReturnsFalseIfShipsExist(): void
    {
        $shipBuildPlan = $this->mock(ShipBuildplanInterface::class);

        $planId = 666;

        $shipBuildPlan->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($planId);
        $shipBuildPlan->shouldReceive('getShipCount')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->colonyShipQueueRepository->shouldReceive('getCountByBuildplan')
            ->with($planId)
            ->once()
            ->andReturn(0);

        static::assertFalse(
            $this->subject->isDeletable($shipBuildPlan)
        );
    }

    public function testIsDeletableReturnsFalseIfQueuedShipsExist(): void
    {
        $shipBuildPlan = $this->mock(ShipBuildplanInterface::class);

        $planId = 666;

        $shipBuildPlan->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($planId);
        $shipBuildPlan->shouldReceive('getShipCount')
            ->withNoArgs()
            ->once()
            ->andReturn(0);

        $this->colonyShipQueueRepository->shouldReceive('getCountByBuildplan')
            ->with($planId)
            ->once()
            ->andReturn(42);

        static::assertFalse(
            $this->subject->isDeletable($shipBuildPlan)
        );
    }

    public function testIsDeletableReturnsTrueIfDeletable(): void
    {
        $shipBuildPlan = $this->mock(ShipBuildplanInterface::class);

        $planId = 666;

        $shipBuildPlan->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($planId);
        $shipBuildPlan->shouldReceive('getShipCount')
            ->withNoArgs()
            ->once()
            ->andReturn(0);

        $this->colonyShipQueueRepository->shouldReceive('getCountByBuildplan')
            ->with($planId)
            ->once()
            ->andReturn(0);

        static::assertTrue(
            $this->subject->isDeletable($shipBuildPlan)
        );
    }
}
