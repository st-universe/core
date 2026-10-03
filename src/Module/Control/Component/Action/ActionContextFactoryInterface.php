<?php

declare(strict_types=1);

namespace Stu\Module\Control\Component\Action;

use Stu\Module\Control\GameControllerInterface;

interface ActionContextFactoryInterface {

    public function createActionContext(GameControllerInterface $game): ActionControllerContext;
}
