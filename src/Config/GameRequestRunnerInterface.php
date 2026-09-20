<?php

declare(strict_types=1);

namespace Stu\Config;

use Stu\Component\Game\ModuleEnum;

interface GameRequestRunnerInterface
{
    public function run(ModuleEnum $module): void;
}
