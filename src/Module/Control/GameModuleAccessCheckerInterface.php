<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Stu\Component\Game\ModuleEnum;

interface GameModuleAccessCheckerInterface
{
    public function isAllowed(ModuleEnum $module, GameControllerInterface $game): bool;
}
