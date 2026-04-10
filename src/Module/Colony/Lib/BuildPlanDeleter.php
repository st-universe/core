<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\ShipyardShipQueueRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;

/**
 * Provides service methods for ship buildplan deletion
 */
final class BuildPlanDeleter implements BuildPlanDeleterInterface
{
    public function __construct(
        private SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository,
        private BuildplanModuleRepositoryInterface $buildplanModuleRepository,
        private ColonyShipQueueRepositoryInterface $colonyShipQueueRepository,
        private ShipyardShipQueueRepositoryInterface $shipyardShipQueueRepository
    ) {}

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
        if ($spacecraftBuildplan->getSpacecraftCount() > 0) {
            return false;
        }

        $buildplanId = $spacecraftBuildplan->getId();
        $queuedShipsCount = $this->colonyShipQueueRepository->getCountByBuildplan($buildplanId);

        if ($queuedShipsCount > 0) {
            return false;
        }

        return $this->shipyardShipQueueRepository->getCountByBuildplan($buildplanId) === 0;
    }
}
