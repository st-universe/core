<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Mockery\MockInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\DealsRepositoryInterface;
use Stu\Orm\Repository\ShipyardShipQueueRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class BuildPlanDeleterTest extends StuTestCase
{
    private MockInterface&SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository;

    private MockInterface&BuildplanModuleRepositoryInterface $buildplanModuleRepository;

    private MockInterface&ColonyShipQueueRepositoryInterface $colonyShipQueueRepository;

    private MockInterface&ShipyardShipQueueRepositoryInterface $shipyardShipQueueRepository;

    private MockInterface&DealsRepositoryInterface $dealsRepository;

    private MockInterface&UserRepositoryInterface $userRepository;

    private BuildPlanDeleter $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->spacecraftBuildplanRepository = $this->mock(SpacecraftBuildplanRepositoryInterface::class);
        $this->buildplanModuleRepository = $this->mock(BuildplanModuleRepositoryInterface::class);
        $this->colonyShipQueueRepository = $this->mock(ColonyShipQueueRepositoryInterface::class);
        $this->shipyardShipQueueRepository = $this->mock(ShipyardShipQueueRepositoryInterface::class);
        $this->dealsRepository = $this->mock(DealsRepositoryInterface::class);
        $this->userRepository = $this->mock(UserRepositoryInterface::class);

        $this->subject = new BuildPlanDeleter(
            $this->spacecraftBuildplanRepository,
            $this->buildplanModuleRepository,
            $this->colonyShipQueueRepository,
            $this->shipyardShipQueueRepository,
            $this->dealsRepository,
            $this->userRepository
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

        $this->dealsRepository->shouldReceive('hasBuildplan')
            ->with($planId)
            ->once()
            ->andReturnFalse();

        $this->buildplanModuleRepository->shouldReceive('truncateByBuildplan')
            ->with($planId)
            ->once();

        $this->spacecraftBuildplanRepository->shouldReceive('delete')
            ->with($spacecraftBuildplan)
            ->once();

        $this->subject->delete($spacecraftBuildplan);
    }

    public function testDeleteTransfersToForeignBuildplansUserIfDealExists(): void
    {
        $spacecraftBuildplan = $this->mock(SpacecraftBuildplan::class);
        $foreignBuildplansUser = $this->mock(User::class);

        $planId = 666;

        $spacecraftBuildplan->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($planId);
        $spacecraftBuildplan->shouldReceive('setUser')
            ->with($foreignBuildplansUser)
            ->once();

        $this->dealsRepository->shouldReceive('hasBuildplan')
            ->with($planId)
            ->once()
            ->andReturnTrue();

        $this->userRepository->shouldReceive('find')
            ->with(UserConstants::USER_FOREIGN_BUILDPLANS)
            ->once()
            ->andReturn($foreignBuildplansUser);

        $this->spacecraftBuildplanRepository->shouldReceive('save')
            ->with($spacecraftBuildplan)
            ->once();
        $this->spacecraftBuildplanRepository->shouldReceive('delete')
            ->never();
        $this->buildplanModuleRepository->shouldReceive('truncateByBuildplan')
            ->never();

        $this->subject->delete($spacecraftBuildplan);
    }

    public function testIsDeletableReturnsFalseIfShipsExist(): void
    {
        $spacecraftBuildplan = $this->mock(SpacecraftBuildplan::class);

        $spacecraftBuildplan->shouldReceive('getSpacecraftCount')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->colonyShipQueueRepository->shouldNotReceive('getCountByBuildplan');
        $this->shipyardShipQueueRepository->shouldNotReceive('getCountByBuildplan');

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
        $this->shipyardShipQueueRepository->shouldNotReceive('getCountByBuildplan');

        static::assertFalse(
            $this->subject->isDeletable($spacecraftBuildplan)
        );
    }

    public function testIsDeletableReturnsFalseIfShipyardQueuedShipsExist(): void
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
        $this->shipyardShipQueueRepository->shouldReceive('getCountByBuildplan')
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
        $this->shipyardShipQueueRepository->shouldReceive('getCountByBuildplan')
            ->with($planId)
            ->once()
            ->andReturn(0);

        static::assertTrue(
            $this->subject->isDeletable($spacecraftBuildplan)
        );
    }
}
