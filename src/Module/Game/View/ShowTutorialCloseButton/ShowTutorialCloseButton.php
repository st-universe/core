<?php

declare(strict_types=1);

namespace Stu\Module\Game\View\ShowTutorialCloseButton;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowTutorialCloseButton implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_TUTORIAL_CLOSE';

    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateFile('html/tutorial/closebutton.twig');
    }
}
