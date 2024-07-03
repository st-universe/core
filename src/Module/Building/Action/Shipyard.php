<?php

declare(strict_types=1);

namespace Stu\Module\Building\Action;

use Override;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ColonySandboxInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;

final class Shipyard implements BuildingActionHandlerInterface
{
    public function __construct(private ColonyShipQueueRepositoryInterface $colonyShipQueueRepository)
    {
    }

    #[Override]
    public function destruct(int $buildingFunctionId, ColonyInterface $colony): void
    {
        $this->colonyShipQueueRepository->truncateByColonyAndBuildingFunction(
            $colony,
            $buildingFunctionId
        );
    }

    #[Override]
    public function deactivate(int $buildingFunctionId, ColonyInterface|ColonySandboxInterface $host): void
    {
        if ($host instanceof ColonyInterface) {
            $this->colonyShipQueueRepository->stopQueueByColonyAndBuildingFunction($host->getId(), $buildingFunctionId);
        }
    }

    #[Override]
    public function activate(int $buildingFunctionId, ColonyInterface|ColonySandboxInterface $host): void
    {
        if ($host instanceof ColonyInterface) {
            $this->colonyShipQueueRepository->restartQueueByColonyAndBuildingFunction($host->getId(), $buildingFunctionId);
        }
    }
}
