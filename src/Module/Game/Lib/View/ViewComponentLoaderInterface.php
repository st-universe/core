<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View;

use Stu\Component\Game\ModuleEnum;
use Stu\Module\Control\GameControllerInterface;

interface ViewComponentLoaderInterface
{
    public function registerViewComponents(
        ModuleEnum $view,
        GameControllerInterface $game
    ): void;
}
