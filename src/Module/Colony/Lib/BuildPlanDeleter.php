<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Override;
use Stu\Orm\Entity\SpacecraftBuildplanInterface;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;

/**
 * Provides service methods for ship buildplan deletion
 */
final class BuildPlanDeleter implements BuildPlanDeleterInterface
{
    public function __construct(private SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository, private BuildplanModuleRepositoryInterface $buildplanModuleRepository, private ColonyShipQueueRepositoryInterface $colonyShipQueueRepository) {}

    #[Override]
    public function delete(SpacecraftBuildplanInterface $spacecraftBuildplan): void
    {
        $this->buildplanModuleRepository->truncateByBuildplan($spacecraftBuildplan->getId());
        $this->spacecraftBuildplanRepository->delete($spacecraftBuildplan);
    }

    #[Override]
    public function isDeletable(
        SpacecraftBuildplanInterface $spacecraftBuildplan
    ): bool {
        $queuedShipsCount = $this->colonyShipQueueRepository->getCountByBuildplan($spacecraftBuildplan->getId());

        return $spacecraftBuildplan->getShipCount() === 0 && $queuedShipsCount === 0;
    }
}
