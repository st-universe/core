<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\DeletePms;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;

final class DeletePms implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DELETE_PMS';

    public function __construct(
        private DeletePmsRequestInterface $deletePmsRequest,
        private PrivateMessageRepositoryInterface $privateMessageRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $timestamp = time();

        foreach ($this->deletePmsRequest->getDeletionIds() as $messageId) {
            $pm = $this->privateMessageRepository->find($messageId);

            if ($pm === null || $pm->getRecipient()->getId() !== $user->getId()) {
                continue;
            }

            $pm->setDeleted($timestamp);
            $this->privateMessageRepository->save($pm);
        }
        $game->getInfo()->addInformation(_('Die Nachrichten wurden gelöscht'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
