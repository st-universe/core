<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\MovePm;

use Override;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;

final class MovePm implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_MOVE_PM';

    public function __construct(private MovePmRequestInterface $movePmRequest, private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository, private PrivateMessageRepositoryInterface $privateMessageRepository) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $pm = $this->privateMessageRepository->find($this->movePmRequest->getPmId());
        if ($pm === null || $pm->getRecipientId() !== $userId) {
            $game->addInformation(_('Diese Nachricht existiert nicht'));
            return;
        }

        $fromCategory = $pm->getCategory();
        if ($fromCategory->isPMOutDir()) {
            return;
        }

        $destination = $this->privateMessageFolderRepository->find($this->movePmRequest->getDestinationCategoryId());
        if ($destination === null || $destination->getUserId() !== $userId) {
            $game->addInformation(_('Dieser Ordner existiert nicht'));
            return;
        }
        $pm->setCategory($destination);

        $this->privateMessageRepository->save($pm);

        $game->addInformation(_('Die Nachricht wurde verschoben'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
