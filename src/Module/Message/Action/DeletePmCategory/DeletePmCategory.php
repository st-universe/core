<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\DeletePmCategory;

use Override;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;

final class DeletePmCategory implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DELETE_PMCATEGORY';

    public function __construct(
        private DeletePmCategoryRequestInterface $deletePmCategoryRequest,
        private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository,
        private PrivateMessageRepositoryInterface $privateMessageRepository
    ) {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $timestamp = time();

        $folder = $this->privateMessageFolderRepository->find($this->deletePmCategoryRequest->getCategoryId());
        if (
            $folder === null ||
            $folder->getUserId() !== $game->getUser()->getId() ||
            !$folder->isDeleteAble()
        ) {
            return;
        }
        $this->privateMessageRepository->setDeleteTimestampByFolder($folder->getId(), $timestamp);

        $folder->setDeleted($timestamp);
        $this->privateMessageFolderRepository->save($folder);

        $game->addInformation(_('Der Ordner wurde gelöscht'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
