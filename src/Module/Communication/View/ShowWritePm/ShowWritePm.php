<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowWritePm;

use PM;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;

final class ShowWritePm implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'WRITE_PM';

    private $showWritePmRequest;

    private $contactRepository;

    private $privateMessageFolderRepository;

    public function __construct(
        ShowWritePmRequestInterface $showWritePmRequest,
        ContactRepositoryInterface $contactRepository,
        PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository
    ) {
        $this->showWritePmRequest = $showWritePmRequest;
        $this->contactRepository = $contactRepository;
        $this->privateMessageFolderRepository = $privateMessageFolderRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $recipientId = $this->showWritePmRequest->getRecipientId();

        $pm = PM::getPMById($this->showWritePmRequest->getReplyPmId());
        if (!$pm || $pm->getRecipientId() != $userId) {
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

            $correspondence = PM::getObjectsBy(
                sprintf(
                    'WHERE (send_user IN (%d,%d) OR recip_user IN (%d,%d)) AND cat_id IN (%s,%s) ORDER BY date DESC LIMIT 10',
                    $reply->getSenderId(),
                    $reply->getRecipientId(),
                    $reply->getSenderId(),
                    $reply->getRecipientId(),
                    $recipientFolder->getId(),
                    $senderFolder->getId()
                )
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
