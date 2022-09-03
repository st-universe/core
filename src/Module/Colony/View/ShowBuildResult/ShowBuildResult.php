<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBuildResult;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowBuildResult implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_BUILD_RESULT';

    public function handle(GameControllerInterface $game): void
    {
        $game->showMacro('html/sitemacros.xhtml/systeminformation');
    }
}
