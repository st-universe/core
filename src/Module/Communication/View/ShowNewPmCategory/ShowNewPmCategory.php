<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowNewPmCategory;

use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class ShowNewPmCategory implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_NEW_CAT';

    public function handle(GameControllerInterface $game): void
    {
        $game->setPageTitle(_('Ordner anlegen'));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/commmacros.xhtml/newcategory');
    }
}
