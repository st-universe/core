<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Override;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;

/**
 * Provides service methods for ship buildplan deletion
 */
final class BuildPlanDeleter implements BuildPlanDeleterInterface
{
    public function __construct(private ShipBuildplanRepositoryInterface $shipBuildplanRepository, private BuildplanModuleRepositoryInterface $buildplanModuleRepository, private ColonyShipQueueRepositoryInterface $colonyShipQueueRepository)
    {
    }

    #[Override]
    public function delete(ShipBuildplanInterface $shipBuildplan): void
    {
        $this->buildplanModuleRepository->truncateByBuildplan($shipBuildplan->getId());
        $this->shipBuildplanRepository->delete($shipBuildplan);
    }

    #[Override]
    public function isDeletable(
        ShipBuildplanInterface $shipBuildplan
    ): bool {
        $queuedShipsCount = $this->colonyShipQueueRepository->getCountByBuildplan($shipBuildplan->getId());

        return $shipBuildplan->getShipCount() === 0 && $queuedShipsCount === 0;
    }
}
