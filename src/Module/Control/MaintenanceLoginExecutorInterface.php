<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Stu\Component\Game\ModuleEnum;

interface MaintenanceLoginExecutorInterface
{
    public function executeIfRequired(ModuleEnum $module, GameControllerInterface $game): bool;
}
