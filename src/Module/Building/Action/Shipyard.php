<?php

declare(strict_types=1);

namespace Stu\Module\Building\Action;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ColonySandboxInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;

final class Shipyard implements BuildingActionHandlerInterface
{
    private ColonyShipQueueRepositoryInterface $colonyShipQueueRepository;

    public function __construct(
        ColonyShipQueueRepositoryInterface $colonyShipQueueRepository
    ) {
        $this->colonyShipQueueRepository = $colonyShipQueueRepository;
    }

    public function destruct(int $buildingFunctionId, ColonyInterface $colony): void
    {
        $this->colonyShipQueueRepository->truncateByColonyAndBuildingFunction(
            $colony,
            $buildingFunctionId
        );
    }

    public function deactivate(int $buildingFunctionId, ColonyInterface|ColonySandboxInterface $host): void
    {
        if ($host instanceof ColonyInterface) {
            $this->colonyShipQueueRepository->stopQueueByColonyAndBuildingFunction($host->getId(), $buildingFunctionId);
        }
    }

    public function activate(int $buildingFunctionId, ColonyInterface|ColonySandboxInterface $host): void
    {
        if ($host instanceof ColonyInterface) {
            $this->colonyShipQueueRepository->restartQueueByColonyAndBuildingFunction($host->getId(), $buildingFunctionId);
        }
    }
}
