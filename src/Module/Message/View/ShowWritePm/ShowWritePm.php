<?php

declare(strict_types=1);

namespace Stu\Module\Message\View\ShowWritePm;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderItem;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageUiFactoryInterface;
use Stu\Orm\Entity\PrivateMessageFolderInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;

final class ShowWritePm implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'WRITE_PM';

    private ShowWritePmRequestInterface $showWritePmRequest;

    private ContactRepositoryInterface $contactRepository;

    private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository;

    private PrivateMessageRepositoryInterface $privateMessageRepository;

    private PrivateMessageUiFactoryInterface $privateMessageUiFactory;

    public function __construct(
        ShowWritePmRequestInterface $showWritePmRequest,
        ContactRepositoryInterface $contactRepository,
        PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository,
        PrivateMessageUiFactoryInterface $privateMessageUiFactory,
        PrivateMessageRepositoryInterface $privateMessageRepository
    ) {
        $this->showWritePmRequest = $showWritePmRequest;
        $this->contactRepository = $contactRepository;
        $this->privateMessageFolderRepository = $privateMessageFolderRepository;
        $this->privateMessageRepository = $privateMessageRepository;
        $this->privateMessageUiFactory = $privateMessageUiFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $recipientId = $this->showWritePmRequest->getRecipientId();

        $pm = $this->privateMessageRepository->find($this->showWritePmRequest->getReplyPmId());
        if ($pm === null || $pm->getRecipientId() !== $userId) {
            $reply = null;
            $correspondence = null;
        } else {
            $reply = $pm;

            $correspondence = $this->privateMessageRepository->getOrderedCorrepondence(
                $reply->getSenderId(),
                $reply->getRecipientId(),
                [PrivateMessageFolderSpecialEnum::PM_SPECIAL_MAIN, PrivateMessageFolderSpecialEnum::PM_DEFAULT_OWN],
                10
            );
        }

        $game->setTemplateFile('html/writepm.xhtml');
        $game->setPageTitle('Neue private Nachricht');
        $game->appendNavigationPart(
            sprintf('pm.php?%s=1', static::VIEW_IDENTIFIER),
            'Private Nachrichte verfassen'
        );
        $game->setTemplateVar(
            'RECIPIENT_ID',
            $recipientId === 0 ? '' : $recipientId
        );
        $game->setTemplateVar('REPLY', $reply);
        $game->setTemplateVar('CONTACT_LIST', $this->contactRepository->getOrderedByUser($userId));
        $game->setTemplateVar('CORRESPONDENCE', $correspondence);
        $game->setTemplateVar(
            'PM_CATEGORIES',
            array_map(
                fn (PrivateMessageFolderInterface $privateMessageFolder): PrivateMessageFolderItem =>
                $this->privateMessageUiFactory->createPrivateMessageFolderItem($privateMessageFolder),
                $this->privateMessageFolderRepository->getOrderedByUser($userId)
            )
        );
    }
}
