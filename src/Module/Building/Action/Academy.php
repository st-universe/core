<?php

declare(strict_types=1);

namespace Stu\Module\Building\Action;

use Stu\Orm\Repository\CrewTrainingRepositoryInterface;

final class Academy implements BuildingActionHandlerInterface
{
    private $crewTrainingRepository;

    public function __construct(
        CrewTrainingRepositoryInterface $crewTrainingRepository
    ) {
        $this->crewTrainingRepository = $crewTrainingRepository;
    }

    public function destruct(int $building_function_id, int $colony_id): void
    {
        $this->crewTrainingRepository->truncateByColony($colony_id);
    }

    public function deactivate(int $building_function_id, int $colony_id): void
    {
    }

    public function activate(int $building_function_id, int $colony_id): void
    {
    }
}