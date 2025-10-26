<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\DeleteAllPms;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;

final class DeleteAllPms implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DELETE_ALL_PMS';

    public function __construct(private DeleteAllPmsRequestInterface $deleteAllPmsRequest, private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository, private PrivateMessageRepositoryInterface $privateMessageRepository) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $timestamp = time();

        $folder = $this->privateMessageFolderRepository->find($this->deleteAllPmsRequest->getCategoryId());
        if ($folder === null || $folder->getUserId() !== $game->getUser()->getId()) {
            return;
        }
        $this->privateMessageRepository->setDeleteTimestampByFolder($folder->getId(), $timestamp);

        $game->getInfo()->addInformation(_('Der Ordner wurde geleert'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
