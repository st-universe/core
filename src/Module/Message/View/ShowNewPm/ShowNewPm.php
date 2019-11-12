<?php

declare(strict_types=1);

namespace Stu\Module\Message\View\ShowNewPm;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowNewPm implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_NEW_PM';

    public function handle(GameControllerInterface $game): void
    {
        $game->showMacro('html/commmacros.xhtml/newpmnavlet');
    }
}
