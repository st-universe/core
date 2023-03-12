<?php

declare(strict_types=1);

namespace Stu\Module\Building\Action;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\CrewTrainingRepositoryInterface;

final class Academy implements BuildingActionHandlerInterface
{
    private CrewTrainingRepositoryInterface $crewTrainingRepository;

    public function __construct(
        CrewTrainingRepositoryInterface $crewTrainingRepository
    ) {
        $this->crewTrainingRepository = $crewTrainingRepository;
    }

    public function destruct(int $buildingFunctionId, int $colonyId): void
    {
        $this->crewTrainingRepository->truncateByColony($colonyId);
    }

    public function deactivate(int $buildingFunctionId, ColonyInterface $colony): void
    {
    }

    public function activate(int $buildingFunctionId, ColonyInterface $colony): void
    {
    }
}
