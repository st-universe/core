<?php

declare(strict_types=1);

namespace Stu\Module\Building\Action;

use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;

final class Shipyard implements BuildingActionHandlerInterface
{
    private $colonyShipQueueRepository;

    public function __construct(
        ColonyShipQueueRepositoryInterface $colonyShipQueueRepository
    ) {
        $this->colonyShipQueueRepository = $colonyShipQueueRepository;
    }

    public function destruct(int $buildingFunctionId, int $colonyId): void
    {
        $this->colonyShipQueueRepository->truncateByColonyAndBuildingFunction(
            $colonyId,
            $buildingFunctionId
        );
    }

    public function deactivate(int $buildingFunctionId, int $colonyId): void
    {
        $this->colonyShipQueueRepository->stopQueueByColonyAndBuildingFunction($colonyId, $buildingFunctionId);
    }

    public function activate(int $buildingFunctionId, int $colonyId): void
    {
        $this->colonyShipQueueRepository->restartQueueByColonyAndBuildingFunction($colonyId, $buildingFunctionId);
    }
}