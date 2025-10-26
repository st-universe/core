<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;

final class ModuleQueueLib implements ModuleQueueLibInterface
{
    public function __construct(private ModuleQueueRepositoryInterface $moduleQueueRepository, private StorageManagerInterface $storageManager) {}

    #[\Override]
    public function cancelModuleQueues(Colony $colony, BuildingFunctionEnum $buildingFunction): void
    {
        $this->cancelModuleQueuesForBuildingFunctions($colony, [$buildingFunction->value]);
    }

    #[\Override]
    public function cancelModuleQueuesForBuilding(Colony $colony, Building $building): void
    {
        $this->cancelModuleQueuesForBuildingFunctions($colony, $building->getFunctions()->getKeys());
    }

    /**
     * @param array<int> $functionIds
     */
    private function cancelModuleQueuesForBuildingFunctions(Colony $colony, array $functionIds): void
    {
        $queues = $this->moduleQueueRepository->getByColonyAndBuilding($colony->getId(), $functionIds);

        foreach ($queues as $queue) {
            $module = $queue->getModule();
            $count = $queue->getAmount();
            $this->moduleQueueRepository->delete($queue);

            foreach ($module->getCost() as $cost) {
                if ($colony->getStorageSum() >= $colony->getMaxStorage()) {
                    break;
                }

                if ($cost->getAmount() * $count > $colony->getMaxStorage() - $colony->getStorageSum()) {
                    $gc = $colony->getMaxStorage() - $colony->getStorageSum();
                } else {
                    $gc = $count * $cost->getAmount();
                }

                $this->storageManager->upperStorage($colony, $cost->getCommodity(), $gc);
            }
        }
    }
}
