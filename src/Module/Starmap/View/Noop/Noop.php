<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View\Noop;

use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class Noop implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'NOOP';

    public function handle(GameControllerInterface $game): void
    {
        $game->showMacro('html/sitemacros.xhtml/noop');
    }
}