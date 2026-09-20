<?php

declare(strict_types=1);

namespace Stu\Module\Control\Component;

use Stu\Component\Game\ModuleEnum;
use Stu\Module\Control\GameControllerInterface;

interface CallbackExecutionInterface
{
    public function execute(ModuleEnum $module, GameControllerInterface $game): void;
}