<?php

declare(strict_types=1);

namespace Stu\Module\Message\View\ShowNewPmCategory;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowNewPmCategory implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_NEW_CAT';

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setPageTitle(_('Ordner anlegen'));
        $game->setMacroInAjaxWindow('html/commmacros.xhtml/newcategory');
    }
}
