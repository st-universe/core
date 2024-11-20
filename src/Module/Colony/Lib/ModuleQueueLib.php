<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Override;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Orm\Entity\BuildingInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;

final class ModuleQueueLib implements ModuleQueueLibInterface
{
    public function __construct(private ModuleQueueRepositoryInterface $moduleQueueRepository, private ColonyStorageManagerInterface $colonyStorageManager) {}

    #[Override]
    public function cancelModuleQueues(ColonyInterface $colony, BuildingFunctionEnum $buildingFunction): void
    {
        $this->cancelModuleQueuesForBuildingFunctions($colony, [$buildingFunction->value]);
    }

    #[Override]
    public function cancelModuleQueuesForBuilding(ColonyInterface $colony, BuildingInterface $building): void
    {
        $this->cancelModuleQueuesForBuildingFunctions($colony, $building->getFunctions()->getKeys());
    }

    /**
     * @param array<int> $functionIds
     */
    private function cancelModuleQueuesForBuildingFunctions(ColonyInterface $colony, array $functionIds): void
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

                $this->colonyStorageManager->upperStorage($colony, $cost->getCommodity(), $gc);
            }
        }
    }
}
