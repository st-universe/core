<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\EditPmCategory;

use Stu\Exception\AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\View\ShowPmCategoryList\ShowPmCategoryList;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;

final class EditPmCategory implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_EDIT_PMCATEGORY_NAME';

    private EditPmCategoryRequestInterface $editPmCategoryRequest;

    private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository;

    public function __construct(
        EditPmCategoryRequestInterface $editPmCategoryRequest,
        PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository
    ) {
        $this->editPmCategoryRequest = $editPmCategoryRequest;
        $this->privateMessageFolderRepository = $privateMessageFolderRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowPmCategoryList::VIEW_IDENTIFIER);

        $name = $this->editPmCategoryRequest->getName();
        if (mb_strlen($name) < 1) {
            return;
        }

        $cat = $this->privateMessageFolderRepository->find($this->editPmCategoryRequest->getCategoryId());
        if ($cat === null || $cat->getUserId() !== $game->getUser()->getId()) {
            throw new AccessViolation();
        }

        $cat->setDescription($name);

        $this->privateMessageFolderRepository->save($cat);

        $game->setTemplateVar('CATEGORY', $cat);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
