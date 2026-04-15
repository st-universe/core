<?php

declare(strict_types=1);

namespace Stu\Module\Building\Action;

use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonySandbox;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;

final class Shipyard implements BuildingActionHandlerInterface
{
    public function __construct(private ColonyShipQueueRepositoryInterface $colonyShipQueueRepository) {}

    #[\Override]
    public function destruct(BuildingFunctionEnum $buildingFunction, Colony $colony): void
    {
        $this->colonyShipQueueRepository->truncateByColonyAndBuildingFunction(
            $colony,
            $buildingFunction
        );
    }

    #[\Override]
    public function deactivate(BuildingFunctionEnum $buildingFunction, Colony|ColonySandbox $host): void
    {
        if ($host instanceof Colony) {
            $this->colonyShipQueueRepository->stopQueueByColonyAndBuildingFunction($host->getId(), $buildingFunction);
        }
    }

    #[\Override]
    public function activate(BuildingFunctionEnum $buildingFunction, Colony|ColonySandbox $host): void
    {
        if ($host instanceof Colony) {
            $this->colonyShipQueueRepository->restartQueueByColonyAndBuildingFunction($host->getId(), $buildingFunction);
        }
    }
}
