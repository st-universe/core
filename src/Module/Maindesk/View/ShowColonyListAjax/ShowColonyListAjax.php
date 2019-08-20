<?php

declare(strict_types=1);

namespace Stu\Module\Maindesk\View\ShowColonyListAjax;

use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class ShowColonyListAjax implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_COLONYLIST_AJAX';

    public function __construct()
    {
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateFile('html/ajaxempty.xhtml');
        $game->setAjaxMacro('html/sitemacros.xhtml/colonylist');
    }
}
