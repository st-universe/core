<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\AddPmCategory;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Communication\View\ShowPmCategoryList\ShowPmCategoryList;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;

final class AddPmCategory implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ADD_PMCATEGORY';

    private $addPmCategoryRequest;

    private $privateMessageFolderRepository;

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
        $cat = $this->privateMessageFolderRepository->prototype();
        $cat->setUserId($game->getUser()->getId());
        $cat->appendToSorting();
        $cat->setDescription($name);

        $this->privateMessageFolderRepository->save($cat);

        $game->setTemplateVar('CATEGORY', $cat);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
