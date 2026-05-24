<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use InvalidArgumentException;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\DealsRepositoryInterface;
use Stu\Orm\Repository\ShipyardShipQueueRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

/**
 * Provides service methods for ship buildplan deletion
 */
final class BuildPlanDeleter implements BuildPlanDeleterInterface
{
    public function __construct(
        private SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository,
        private BuildplanModuleRepositoryInterface $buildplanModuleRepository,
        private ColonyShipQueueRepositoryInterface $colonyShipQueueRepository,
        private ShipyardShipQueueRepositoryInterface $shipyardShipQueueRepository,
        private DealsRepositoryInterface $dealsRepository,
        private UserRepositoryInterface $userRepository
    ) {}

    #[\Override]
    public function delete(SpacecraftBuildplan $spacecraftBuildplan): void
    {
        $buildplanId = $spacecraftBuildplan->getId();
        if ($this->dealsRepository->hasBuildplan($buildplanId)) {
            $this->transferToForeignBuildplansUser($spacecraftBuildplan);
            return;
        }

        $this->buildplanModuleRepository->truncateByBuildplan($buildplanId);
        $this->spacecraftBuildplanRepository->delete($spacecraftBuildplan);
    }

    private function transferToForeignBuildplansUser(SpacecraftBuildplan $spacecraftBuildplan): void
    {
        $user = $this->userRepository->find(UserConstants::USER_FOREIGN_BUILDPLANS);
        if ($user === null) {
            throw new InvalidArgumentException(sprintf('user with id %d does not exist', UserConstants::USER_FOREIGN_BUILDPLANS));
        }

        $spacecraftBuildplan->setUser($user);
        $this->spacecraftBuildplanRepository->save($spacecraftBuildplan);
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
