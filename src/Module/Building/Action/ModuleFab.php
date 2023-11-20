<?php

declare(strict_types=1);

namespace Stu\Module\Building\Action;

use Stu\Module\Colony\Lib\ModuleQueueLibInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ColonySandboxInterface;

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

    public function deactivate(int $buildingFunctionId, ColonyInterface|ColonySandboxInterface $host): void
    {
        if ($host instanceof ColonyInterface) {
            $this->moduleQueueLib->cancelModuleQueues($host, $buildingFunctionId);
        }
    }

    public function activate(int $buildingFunctionId, ColonyInterface|ColonySandboxInterface $host): void
    {
        //nothing to do here
    }
}
