<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowContactMode;

use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class ShowContactMode implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_CONTACT_MODE';

    public function handle(GameControllerInterface $game): void
    {
        $game->showMacro('html/commmacros.xhtml/clmodeview');
    }
}
