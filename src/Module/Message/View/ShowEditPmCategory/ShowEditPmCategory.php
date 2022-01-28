<?php

declare(strict_types=1);

namespace Stu\Module\Message\View\ShowEditPmCategory;

use Stu\Exception\AccessViolation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;

final class ShowEditPmCategory implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_EDIT_CAT';

    private ShowEditCategoryRequestInterface $showEditCategoryRequest;

    private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository;

    public function __construct(
        ShowEditCategoryRequestInterface $showEditCategoryRequest,
        PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository
    ) {
        $this->showEditCategoryRequest = $showEditCategoryRequest;
        $this->privateMessageFolderRepository = $privateMessageFolderRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $category = $this->privateMessageFolderRepository->find($this->showEditCategoryRequest->getCategoryId());

        if ($category === null || $category->getUserId() != $game->getUser()->getId()) {
            throw new AccessViolation();
        }

        $game->setPageTitle(_('Ordner editieren'));
        $game->setMacroInAjaxWindow('html/commmacros.xhtml/editcategory');

        $game->setTemplateVar('PM_CATEGORY', $category);
    }
}
