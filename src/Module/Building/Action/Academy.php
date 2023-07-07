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

    public function destruct(int $buildingFunctionId, ColonyInterface $colony): void
    {
        $this->crewTrainingRepository->truncateByColony($colony);
    }

    public function deactivate(int $buildingFunctionId, ColonyInterface $colony): void
    {
    }

    public function activate(int $buildingFunctionId, ColonyInterface $colony): void
    {
    }
}
