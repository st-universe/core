<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\DeleteAllPms;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;

final class DeleteAllPms implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DELETE_ALL_PMS';

    private DeleteAllPmsRequestInterface $deleteAllPmsRequest;

    private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository;

    private PrivateMessageRepositoryInterface $privateMessageRepository;

    public function __construct(
        DeleteAllPmsRequestInterface $deleteAllPmsRequest,
        PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository,
        PrivateMessageRepositoryInterface $privateMessageRepository
    ) {
        $this->deleteAllPmsRequest = $deleteAllPmsRequest;
        $this->privateMessageFolderRepository = $privateMessageFolderRepository;
        $this->privateMessageRepository = $privateMessageRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $timestamp = time();

        $folder = $this->privateMessageFolderRepository->find($this->deleteAllPmsRequest->getCategoryId());
        if ($folder === null || $folder->getUserId() !== $game->getUser()->getId()) {
            return;
        }
        $this->privateMessageRepository->setDeleteTimestampByFolder($folder->getId(), $timestamp);

        $game->addInformation(_('Der Ordner wurde geleert'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
