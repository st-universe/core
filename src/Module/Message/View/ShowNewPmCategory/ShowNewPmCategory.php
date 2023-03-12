<?php

declare(strict_types=1);

namespace Stu\Module\Message\View\ShowNewPmCategory;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowNewPmCategory implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_NEW_CAT';

    public function handle(GameControllerInterface $game): void
    {
        $game->setPageTitle(_('Ordner anlegen'));
        $game->setMacroInAjaxWindow('html/commmacros.xhtml/newcategory');
    }
}
