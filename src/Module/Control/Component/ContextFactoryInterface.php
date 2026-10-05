<?php

declare(strict_types=1);

namespace Stu\Module\Control\Component;

use Stu\Module\Control\GameControllerInterface;

interface ContextFactoryInterface
{
    public function createContext(GameControllerInterface $game): ControllerContext;
}
