<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\MarkPmsRead;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;

final class MarkPmsRead implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_MARK_PMS_READ';

    public function __construct(
        private MarkPmsReadRequestInterface $markPmsReadRequest,
        private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository,
        private PrivateMessageRepositoryInterface $privateMessageRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $folder = $this->privateMessageFolderRepository->find($this->markPmsReadRequest->getCategoryId());
        if ($folder === null || $folder->getUserId() !== $game->getUser()->getId()) {
            return;
        }

        $this->privateMessageRepository->markAsReadByFolder($folder->getId());

        $game->getInfo()->addInformation(_('Alle Nachrichten im Ordner wurden als gelesen markiert'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
