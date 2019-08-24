<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowEditPmCategory;

use AccessViolation;
use PMCategory;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class ShowEditPmCategory implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_EDIT_CAT';

    private $showEditCategoryRequest;

    public function __construct(
        ShowEditCategoryRequestInterface $showEditCategoryRequest
    ) {
        $this->showEditCategoryRequest = $showEditCategoryRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $category = new PMCategory($this->showEditCategoryRequest->getCategoryId());

        if ($category->getUserId() != $game->getUser()->getId()) {
            throw new AccessViolation();
        }

        $game->setPageTitle(_('Ordner editieren'));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setAjaxMacro('html/commmacros.xhtml/editcategory');

        $game->setTemplateVar('PM_CATEGORY', $category);
    }
}
