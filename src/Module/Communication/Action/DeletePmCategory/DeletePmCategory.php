<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\DeletePmCategory;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;

final class DeletePmCategory implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DELETE_PMCATEGORY';

    private $deletePmCategoryRequest;

    private $privateMessageFolderRepository;

    public function __construct(
        DeletePmCategoryRequestInterface $deletePmCategoryRequest,
        PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository
    ) {
        $this->deletePmCategoryRequest = $deletePmCategoryRequest;
        $this->privateMessageFolderRepository = $privateMessageFolderRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $cat = $this->privateMessageFolderRepository->find($this->deletePmCategoryRequest->getCategoryId());
        if (
            $cat === null ||
            $cat->getUserId() != $game->getUser()->getId() ||
            !$cat->isDeleteAble()
        ) {
            return;
        }
        $cat->truncate();

        $this->privateMessageFolderRepository->delete($cat);

        $game->addInformation(_('Der Ordner wurde gelöscht'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
