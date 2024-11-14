<?php

declare(strict_types=1);

namespace Stu\Module\Building\Action;

use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ColonySandboxInterface;

interface BuildingActionHandlerInterface
{
    public function destruct(BuildingFunctionEnum $buildingFunction, ColonyInterface $colony): void;

    public function deactivate(
        BuildingFunctionEnum $buildingFunction,
        ColonyInterface|ColonySandboxInterface $host
    ): void;

    public function activate(
        BuildingFunctionEnum $buildingFunction,
        ColonyInterface|ColonySandboxInterface $host
    ): void;
}
