<?php

declare(strict_types=1);

namespace Stu\Module\Building\Action;

use Stu\Module\Colony\Lib\ModuleQueueLibInterface;
use Stu\Orm\Entity\ColonyInterface;

final class ModuleFab implements BuildingActionHandlerInterface
{
    private ModuleQueueLibInterface $moduleQueueLib;

    public function __construct(
        ModuleQueueLibInterface $moduleQueueLib
    ) {
        $this->moduleQueueLib = $moduleQueueLib;
    }

    public function destruct(int $buildingFunctionId, ColonyInterface $colony): void
    {
        //nothing to do here
    }

    public function deactivate(int $buildingFunctionId, ColonyInterface $colony): void
    {
        $this->moduleQueueLib->cancelModuleQueues($colony, $buildingFunctionId);
    }

    public function activate(int $buildingFunctionId, ColonyInterface $colony): void
    {
        //nothing to do here
    }
}
