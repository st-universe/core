<?php

declare(strict_types=1);

namespace Stu\Module\Building\Action;

interface BuildingActionHandlerInterface
{
    public function destruct(int $buildingFunctionId, int $colonyId): void;

    public function deactivate(int $buildingFunctionId, int $colonyId): void;

    public function activate(int $buildingFunctionId, int $colonyId): void;
}