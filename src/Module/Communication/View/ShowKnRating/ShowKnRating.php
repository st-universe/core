<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnRating;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowKnRating implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_KN_RATING';

    public function handle(GameControllerInterface $game): void
    {
        $game->showMacro('html/commmacros.xhtml/knrating');
    }
}
