<?php

declare(strict_types=1);

namespace Stu\Module\Building\Action;

use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonySandbox;

interface BuildingActionHandlerInterface
{
    public function destruct(BuildingFunctionEnum $buildingFunction, Colony $colony): void;

    public function deactivate(
        BuildingFunctionEnum $buildingFunction,
        Colony|ColonySandbox $host
    ): void;

    public function activate(
        BuildingFunctionEnum $buildingFunction,
        Colony|ColonySandbox $host
    ): void;
}
