<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\DeletePms;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;

final class DeletePms implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DELETE_PMS';

    private DeletePmsRequestInterface $deletePmsRequest;

    private PrivateMessageRepositoryInterface $privateMessageRepository;

    public function __construct(
        DeletePmsRequestInterface $deletePmsRequest,
        PrivateMessageRepositoryInterface $privateMessageRepository
    ) {
        $this->deletePmsRequest = $deletePmsRequest;
        $this->privateMessageRepository = $privateMessageRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $timestamp = time();

        foreach ($this->deletePmsRequest->getDeletionIds() as $messageId) {
            $pm = $this->privateMessageRepository->find($messageId);

            if ($pm === null || $pm->getRecipient() !== $user) {
                continue;
            }

            $pm->setDeleted($timestamp);
            $this->privateMessageRepository->save($pm);
        }
        $game->addInformation(_('Die Nachrichten wurden gelöscht'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
