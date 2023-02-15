<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;

/**
 * Provides service methods for ship buildplan deletion
 */
final class BuildPlanDeleter implements BuildPlanDeleterInterface
{
    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    private BuildplanModuleRepositoryInterface $buildplanModuleRepository;

    private ColonyShipQueueRepositoryInterface $colonyShipQueueRepository;

    public function __construct(
        ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        BuildplanModuleRepositoryInterface $buildplanModuleRepository,
        ColonyShipQueueRepositoryInterface $colonyShipQueueRepository
    ) {
        $this->shipBuildplanRepository = $shipBuildplanRepository;
        $this->buildplanModuleRepository = $buildplanModuleRepository;
        $this->colonyShipQueueRepository = $colonyShipQueueRepository;
    }

    public function delete(ShipBuildplanInterface $shipBuildplan): void
    {
        $this->buildplanModuleRepository->truncateByBuildplan($shipBuildplan->getId());
        $this->shipBuildplanRepository->delete($shipBuildplan);
    }

    public function isDeletable(
        ShipBuildplanInterface $shipBuildplan
    ): bool {
        $queuedShipsCount = $this->colonyShipQueueRepository->getCountByBuildplan($shipBuildplan->getId());

        return $shipBuildplan->getShipCount() === 0 && $queuedShipsCount === 0;
    }
}
