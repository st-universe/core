<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\MovePm;

use PM;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;

final class MovePm implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_MOVE_PM';

    private $movePmRequest;

    private $privateMessageFolderRepository;

    public function __construct(
        MovePmRequestInterface $movePmRequest,
        PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository
    ) {
        $this->movePmRequest = $movePmRequest;
        $this->privateMessageFolderRepository = $privateMessageFolderRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $cat = $this->privateMessageFolderRepository->find($this->movePmRequest->getCategoryId());

        if ($cat === null || $cat->isPMOutDir()) {
            return;
        }

        $destination = $this->privateMessageFolderRepository->find($this->movePmRequest->getDestinationCategoryId());
        $pm = new PM($this->movePmRequest->getPmId());

        if ($destination === null || $destination->getUserId() != $game->getUser()->getId()) {
            $game->addInformation(_('Dieser Ordner existiert nicht'));
            return;
        }
        if (!$pm->isOwnPM()) {
            $game->addInformation(_('Diese Nachricht existiert nicht'));
            return;
        }
        $pm->setCategoryId($destination->getId());
        $pm->save();

        $game->addInformation(_('Die Nachricht wurde verscheben'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
