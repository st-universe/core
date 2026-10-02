<?php

declare(strict_types=1);

namespace Stu\Module\Control\Component\View;

use Stu\Component\Game\ModuleEnum;
use Stu\Module\Control\GameControllerInterface;

interface ViewContextFactoryInterface {

    public function createViewContext(GameControllerInterface $game, ModuleEnum $module): ViewContext;
}
