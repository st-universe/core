<?php

declare(strict_types=1);

namespace Stu\Module\Message\View\ShowWritePm;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderItem;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageUiFactoryInterface;
use Stu\Orm\Entity\PrivateMessageFolderInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;

final class ShowWritePm implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'WRITE_PM';

    public function __construct(
        private ShowWritePmRequestInterface $showWritePmRequest,
        private ContactRepositoryInterface $contactRepository,
        private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository,
        private PrivateMessageUiFactoryInterface $privateMessageUiFactory,
        private PrivateMessageRepositoryInterface $privateMessageRepository
    ) {}

    #[Override]
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
                [PrivateMessageFolderTypeEnum::SPECIAL_MAIN->value, PrivateMessageFolderTypeEnum::DEFAULT_OWN->value],
                10
            );
        }

        $game->setViewTemplate('html/message/writePm.twig');
        $game->setPageTitle('Neue private Nachricht');
        $game->appendNavigationPart(
            sprintf('pm.php?%s=1', self::VIEW_IDENTIFIER),
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
                fn(PrivateMessageFolderInterface $privateMessageFolder): PrivateMessageFolderItem =>
                $this->privateMessageUiFactory->createPrivateMessageFolderItem($privateMessageFolder),
                $this->privateMessageFolderRepository->getOrderedByUser($userId)
            )
        );
    }
}
