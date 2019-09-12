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

    public function destruct(int $building_function_id, int $colony_id): void
    {
        $this->colonyShipQueueRepository->truncateByColonyAndBuildingFunction(
            $colony_id,
            $building_function_id
        );
    }

    public function deactivate(int $building_function_id, int $colony_id): void
    {
        $this->colonyShipQueueRepository->stopQueueByColonyAndBuildingFunction($colony_id, $building_function_id);
    }

    public function activate(int $building_function_id, int $colony_id): void
    {
        $this->colonyShipQueueRepository->restartQueueByColonyAndBuildingFunction($colony_id, $building_function_id);
    }
}