<?php

declare(strict_types=1);

namespace Stu\Module\Message\View\ShowIgnore;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowIgnore implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_IGNORE';

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->showMacro('html/macros.xhtml/ignoretext');
    }
}
