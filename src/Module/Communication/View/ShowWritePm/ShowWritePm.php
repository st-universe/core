<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowWritePm;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;

final class ShowWritePm implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'WRITE_PM';

    private $showWritePmRequest;

    private $contactRepository;

    private $privateMessageFolderRepository;

    private $privateMessageRepository;

    public function __construct(
        ShowWritePmRequestInterface $showWritePmRequest,
        ContactRepositoryInterface $contactRepository,
        PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository,
        PrivateMessageRepositoryInterface $privateMessageRepository
    ) {
        $this->showWritePmRequest = $showWritePmRequest;
        $this->contactRepository = $contactRepository;
        $this->privateMessageFolderRepository = $privateMessageFolderRepository;
        $this->privateMessageRepository = $privateMessageRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $recipientId = $this->showWritePmRequest->getRecipientId();

        $pm = $this->privateMessageRepository->find($this->showWritePmRequest->getReplyPmId());
        if ($pm === null || $pm->getRecipientId() != $userId) {
            $reply = null;
            $correspondence = null;
        } else {
            $reply = $pm;

            $recipientFolder = $this->privateMessageFolderRepository->getByUserAndSpecial(
                (int) $reply->getRecipientId(),
                PM_SPECIAL_MAIN
            );
            $senderFolder = $this->privateMessageFolderRepository->getByUserAndSpecial(
                (int) $reply->getSenderId(),
                PM_SPECIAL_MAIN
            );

            $correspondence = $this->privateMessageRepository->getOrderedCorrepondence(
                [$reply->getSenderId(), $reply->getRecipientId()],
                [$recipientFolder->getId(), $senderFolder->getId()],
                10
            );
        }

        $game->setTemplateFile('html/writepm.xhtml');
        $game->setPageTitle('Neue private Nachricht');
        $game->appendNavigationPart(
            sprintf('comm.php?%s=1', static::VIEW_IDENTIFIER),
            'Private Nachrichte verfassen'
        );

        $game->setTemplateVar(
            'RECIPIENT_ID',
            $recipientId === 0 ? '' : $recipientId
        );
        $game->setTemplateVar('REPLY', $reply);
        $game->setTemplateVar('CONTACT_LIST', $this->contactRepository->getOrderedByUser($userId));
        $game->setTemplateVar('CORRESPONDENCE', $correspondence);
        $game->setTemplateVar('PM_CATEGORIES', $this->privateMessageFolderRepository->getOrderedByUser($userId));
    }
}
