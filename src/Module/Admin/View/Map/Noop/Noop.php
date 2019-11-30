<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Map\Noop;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class Noop implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'NOOP';

    public function handle(GameControllerInterface $game): void
    {
        $game->showMacro('../html/sitemacros.xhtml/noop');
    }
}
