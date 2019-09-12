<?php

declare(strict_types=1);

namespace Stu\Module\Building\Action;

interface BuildingActionHandlerInterface
{
    public function destruct(int $building_function_id, int $colony_id): void;

    public function deactivate(int $building_function_id, int $colony_id): void;

    public function activate(int $building_function_id, int $colony_id): void;
}