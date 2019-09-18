<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\DeletePms;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;

final class DeletePms implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DELETE_PMS';

    private $deletePmsRequest;

    private $privateMessageRepository;

    public function __construct(
        DeletePmsRequestInterface $deletePmsRequest,
        PrivateMessageRepositoryInterface $privateMessageRepository
    ) {
        $this->deletePmsRequest = $deletePmsRequest;
        $this->privateMessageRepository = $privateMessageRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        foreach ($this->deletePmsRequest->getIgnoreIds() as $messageId) {
            $pm = $this->privateMessageRepository->find($messageId);

            if ($pm === null || $pm->getRecipientId() !== $userId) {
                continue;
            }

            $this->privateMessageRepository->delete($pm);
        }
        $game->addInformation(_('Die Nachrichten wurden gelöscht'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
