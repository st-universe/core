<?php

declare(strict_types=1);

namespace Stu\Module\Building\Action;

use Override;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonySandbox;
use Stu\Orm\Repository\CrewTrainingRepositoryInterface;

final class Academy implements BuildingActionHandlerInterface
{
    public function __construct(private CrewTrainingRepositoryInterface $crewTrainingRepository) {}

    #[Override]
    public function destruct(BuildingFunctionEnum $buildingFunction, Colony $colony): void
    {
        $this->crewTrainingRepository->truncateByColony($colony);
    }

    #[Override]
    public function deactivate(BuildingFunctionEnum $buildingFunction, Colony|ColonySandbox $host): void
    {
        // nothing to do here
    }

    #[Override]
    public function activate(BuildingFunctionEnum $buildingFunction, Colony|ColonySandbox $host): void
    {
        // nothing to do here
    }
}
