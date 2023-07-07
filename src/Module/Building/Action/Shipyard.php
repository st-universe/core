<?php

declare(strict_types=1);

namespace Stu\Module\Building\Action;

use Stu\Orm\Entity\ColonyInterface;
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

    public function deactivate(int $buildingFunctionId, ColonyInterface $colony): void
    {
        $this->colonyShipQueueRepository->stopQueueByColonyAndBuildingFunction($colony->getId(), $buildingFunctionId);
    }

    public function activate(int $buildingFunctionId, ColonyInterface $colony): void
    {
        $this->colonyShipQueueRepository->restartQueueByColonyAndBuildingFunction($colony->getId(), $buildingFunctionId);
    }
}
