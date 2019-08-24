<?php

declare(strict_types=1);

namespace Stu\Module\Index\View\ShowHelp;

use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class ShowHelp implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_HELP';

    public function handle(GameControllerInterface $game): void
    {
        $game->setPageTitle(_('Hilfe - Star Trek Universe'));
        $game->setTemplateFile('html/index_help.xhtml');
    }
}
