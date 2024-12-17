<?php

namespace Stu\Lib\Component;

use Stu\Lib\Component\ComponentInterface;
use Stu\Module\Control\GameControllerInterface;

interface ComponentRendererInterface
{
    public function renderComponent(
        ComponentInterface $component,
        GameControllerInterface $game
    ): void;
}
