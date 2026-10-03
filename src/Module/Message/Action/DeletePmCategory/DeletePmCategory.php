<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\DeletePmCategory;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;

final class DeletePmCategory implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DELETE_PMCATEGORY';

    public function __construct(
        private DeletePmCategoryRequestInterface $deletePmCategoryRequest,
        private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository,
        private PrivateMessageRepositoryInterface $privateMessageRepository
    ) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $timestamp = time();

        $folder = $this->privateMessageFolderRepository->find($this->deletePmCategoryRequest->getCategoryId());
        if (
            $folder === null ||
            $folder->getUserId() !== $context->getUser()->getId() ||
            !$folder->isDeleteAble()
        ) {
            return;
        }
        $this->privateMessageRepository->setDeleteTimestampByFolder($folder->getId(), $timestamp);

        $folder->setDeleted($timestamp);
        $this->privateMessageFolderRepository->save($folder);

        $context->getInfo()->addInformation(_('Der Ordner wurde gelöscht'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
