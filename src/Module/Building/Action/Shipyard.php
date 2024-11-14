<?php

declare(strict_types=1);

namespace Stu\Module\Building\Action;

use Override;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ColonySandboxInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;

final class Shipyard implements BuildingActionHandlerInterface
{
    public function __construct(private ColonyShipQueueRepositoryInterface $colonyShipQueueRepository) {}

    #[Override]
    public function destruct(BuildingFunctionEnum $buildingFunction, ColonyInterface $colony): void
    {
        $this->colonyShipQueueRepository->truncateByColonyAndBuildingFunction(
            $colony,
            $buildingFunction
        );
    }

    #[Override]
    public function deactivate(BuildingFunctionEnum $buildingFunction, ColonyInterface|ColonySandboxInterface $host): void
    {
        if ($host instanceof ColonyInterface) {
            $this->colonyShipQueueRepository->stopQueueByColonyAndBuildingFunction($host->getId(), $buildingFunction);
        }
    }

    #[Override]
    public function activate(BuildingFunctionEnum $buildingFunction, ColonyInterface|ColonySandboxInterface $host): void
    {
        if ($host instanceof ColonyInterface) {
            $this->colonyShipQueueRepository->restartQueueByColonyAndBuildingFunction($host->getId(), $buildingFunction);
        }
    }
}
