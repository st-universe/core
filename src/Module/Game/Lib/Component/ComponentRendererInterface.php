<?php

namespace Stu\Module\Game\Lib\Component;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\Render\Fragments\RenderFragmentInterface;
use Stu\Orm\Entity\UserInterface;

interface ComponentRendererInterface
{

    public function renderComponent(
        RenderFragmentInterface $renderFragment,
        UserInterface $user,
        GameControllerInterface $game
    ): void;
}
