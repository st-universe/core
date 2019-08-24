<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowNewPm;

use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class ShowNewPm implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_NEW_PM';

    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateFile('html/ajaxempty.xhtml');
        $game->setAjaxMacro('html/commmacros.xhtml/newpmnavlet');
    }
}
