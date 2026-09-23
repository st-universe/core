<?php

declare(strict_types=1);

namespace Stu\Module\Message\View\ShowNewPmCategory;

use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\ViewControllerInterface;

final class ShowNewPmCategory implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_NEW_CAT';

    #[\Override]
    public function handle(ViewControllerContext $game): void
    {
        $game->setPageTitle(_('Ordner anlegen'));
        $game->setMacroInAjaxWindow('html/communication/newCategory.twig');
    }
}
