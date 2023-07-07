<?php

declare(strict_types=1);

namespace Stu\Module\Building\Action;

use Stu\Orm\Entity\ColonyInterface;

interface BuildingActionHandlerInterface
{
    public function destruct(int $buildingFunctionId, ColonyInterface $colony): void;

    public function deactivate(int $buildingFunctionId, ColonyInterface $colony): void;

    public function activate(int $buildingFunctionId, ColonyInterface $colony): void;
}
