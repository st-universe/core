<?php

namespace Stu\Module\Game\Lib;

use Stu\Component\Game\ModuleViewEnum;
use Stu\Module\Control\GameControllerInterface;

interface ViewComponentLoaderInterface
{
    public function registerViewComponents(
        ModuleViewEnum $view,
        GameControllerInterface $game
    ): void;
}
