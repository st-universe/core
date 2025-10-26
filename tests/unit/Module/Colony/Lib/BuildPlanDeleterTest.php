<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Mockery\MockInterface;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\StuTestCase;

class BuildPlanDeleterTest extends StuTestCase
{
    private MockInterface&SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository;

    private MockInterface&BuildplanModuleRepositoryInterface $buildplanModuleRepository;

    private MockInterface&ColonyShipQueueRepositoryInterface $colonyShipQueueRepository;

    private BuildPlanDeleter $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->spacecraftBuildplanRepository = $this->mock(SpacecraftBuildplanRepositoryInterface::class);
        $this->buildplanModuleRepository = $this->mock(BuildplanModuleRepositoryInterface::class);
        $this->colonyShipQueueRepository = $this->mock(ColonyShipQueueRepositoryInterface::class);

        $this->subject = new BuildPlanDeleter(
            $this->spacecraftBuildplanRepository,
            $this->buildplanModuleRepository,
            $this->colonyShipQueueRepository
        );
    }

    public function testDeleteDeletes(): void
    {
        $spacecraftBuildplan = $this->mock(SpacecraftBuildplan::class);

        $planId = 666;

        $spacecraftBuildplan->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($planId);

        $this->buildplanModuleRepository->shouldReceive('truncateByBuildplan')
            ->with($planId)
            ->once();

        $this->spacecraftBuildplanRepository->shouldReceive('delete')
            ->with($spacecraftBuildplan)
            ->once();

        $this->subject->delete($spacecraftBuildplan);
    }

    public function testIsDeletableReturnsFalseIfShipsExist(): void
    {
        $spacecraftBuildplan = $this->mock(SpacecraftBuildplan::class);

        $planId = 666;

        $spacecraftBuildplan->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($planId);
        $spacecraftBuildplan->shouldReceive('getSpacecraftCount')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->colonyShipQueueRepository->shouldReceive('getCountByBuildplan')
            ->with($planId)
            ->once()
            ->andReturn(0);

        static::assertFalse(
            $this->subject->isDeletable($spacecraftBuildplan)
        );
    }

    public function testIsDeletableReturnsFalseIfQueuedShipsExist(): void
    {
        $spacecraftBuildplan = $this->mock(SpacecraftBuildplan::class);

        $planId = 666;

        $spacecraftBuildplan->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($planId);
        $spacecraftBuildplan->shouldReceive('getSpacecraftCount')
            ->withNoArgs()
            ->once()
            ->andReturn(0);

        $this->colonyShipQueueRepository->shouldReceive('getCountByBuildplan')
            ->with($planId)
            ->once()
            ->andReturn(42);

        static::assertFalse(
            $this->subject->isDeletable($spacecraftBuildplan)
        );
    }

    public function testIsDeletableReturnsTrueIfDeletable(): void
    {
        $spacecraftBuildplan = $this->mock(SpacecraftBuildplan::class);

        $planId = 666;

        $spacecraftBuildplan->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($planId);
        $spacecraftBuildplan->shouldReceive('getSpacecraftCount')
            ->withNoArgs()
            ->once()
            ->andReturn(0);

        $this->colonyShipQueueRepository->shouldReceive('getCountByBuildplan')
            ->with($planId)
            ->once()
            ->andReturn(0);

        static::assertTrue(
            $this->subject->isDeletable($spacecraftBuildplan)
        );
    }
}
