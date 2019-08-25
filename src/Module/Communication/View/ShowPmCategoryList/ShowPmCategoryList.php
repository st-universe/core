<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowPmCategoryList;

use PMCategory;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class ShowPmCategoryList implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_CAT_LIST';

    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateVar('markcat', true);
        $game->setTemplateFile('html/ajaxempty.xhtml');
        $game->setAjaxMacro('html/commmacros.xhtml/pmcategorylist_ajax');

        $game->setTemplateVar('PM_CATEGORIES', PMCategory::getCategoryTree($game->getUser()->getId()));
    }
}
