<?php

declare(strict_types=1);

namespace Stu\Module\Building\Action;

use Override;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ColonySandboxInterface;
use Stu\Orm\Repository\CrewTrainingRepositoryInterface;

final class Academy implements BuildingActionHandlerInterface
{
    public function __construct(private CrewTrainingRepositoryInterface $crewTrainingRepository)
    {
    }

    #[Override]
    public function destruct(int $buildingFunctionId, ColonyInterface $colony): void
    {
        $this->crewTrainingRepository->truncateByColony($colony);
    }

    #[Override]
    public function deactivate(int $buildingFunctionId, ColonyInterface|ColonySandboxInterface $host): void
    {
    }

    #[Override]
    public function activate(int $buildingFunctionId, ColonyInterface|ColonySandboxInterface $host): void
    {
    }
}
