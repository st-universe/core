<?php

declare(strict_types=1);

namespace Stu\Module\Message\View\ShowEditPmCategory;

use Stu\Exception\AccessViolationException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;

final class ShowEditPmCategory implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_EDIT_CAT';

    public function __construct(private ShowEditCategoryRequestInterface $showEditCategoryRequest, private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository)
    {
    }

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $category = $this->privateMessageFolderRepository->find($this->showEditCategoryRequest->getCategoryId());

        if ($category === null || $category->getUserId() !== $game->getUser()->getId()) {
            throw new AccessViolationException();
        }

        $game->setPageTitle(_('Ordner editieren'));
        $game->setMacroInAjaxWindow('html/communication/editCategory.twig');

        $game->setTemplateVar('PM_CATEGORY', $category);
    }
}
