<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowInformation;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowInformation implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_INFORMATION';

    public function handle(GameControllerInterface $game): void
    {
        $game->showMacro('html/sitemacros.xhtml/systeminformation');
    }
}
