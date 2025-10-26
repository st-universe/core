<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;

/**
 * Provides service methods for ship buildplan deletion
 */
final class BuildPlanDeleter implements BuildPlanDeleterInterface
{
    public function __construct(private SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository, private BuildplanModuleRepositoryInterface $buildplanModuleRepository, private ColonyShipQueueRepositoryInterface $colonyShipQueueRepository) {}

    #[\Override]
    public function delete(SpacecraftBuildplan $spacecraftBuildplan): void
    {
        $this->buildplanModuleRepository->truncateByBuildplan($spacecraftBuildplan->getId());
        $this->spacecraftBuildplanRepository->delete($spacecraftBuildplan);
    }

    #[\Override]
    public function isDeletable(
        SpacecraftBuildplan $spacecraftBuildplan
    ): bool {
        $queuedShipsCount = $this->colonyShipQueueRepository->getCountByBuildplan($spacecraftBuildplan->getId());

        return $spacecraftBuildplan->getSpacecraftCount() === 0 && $queuedShipsCount === 0;
    }
}
