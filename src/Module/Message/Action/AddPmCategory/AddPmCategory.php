<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\AddPmCategory;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\View\ShowPmCategoryList\ShowPmCategoryList;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;

final class AddPmCategory implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ADD_PMCATEGORY';

    private AddPmCategoryRequestInterface $addPmCategoryRequest;

    private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository;

    public function __construct(
        AddPmCategoryRequestInterface $addPmCategoryRequest,
        PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository
    ) {
        $this->addPmCategoryRequest = $addPmCategoryRequest;
        $this->privateMessageFolderRepository = $privateMessageFolderRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowPmCategoryList::VIEW_IDENTIFIER);

        $name = $this->addPmCategoryRequest->getName();
        if (mb_strlen($name) < 1) {
            return;
        }

        $user = $game->getUser();

        $sort = $this->privateMessageFolderRepository->getMaxOrderIdByUser($user);

        $cat = $this->privateMessageFolderRepository->prototype();
        $cat->setUser($user);
        $cat->setDescription($name);
        $cat->setSort($sort + 1);

        $this->privateMessageFolderRepository->save($cat);

        $game->setTemplateVar('CATEGORY', $cat);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
