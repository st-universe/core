<?php

declare(strict_types=1);

namespace Stu\Module\Message\View\ShowWritePm;

use Override;
use Stu\Component\Game\GameEnum;
use Stu\Component\Player\Settings\UserSettingsProviderInterface;
use Stu\Lib\Component\ComponentRegistrationInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Game\Component\GameComponentEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderItem;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageListItem;
use Stu\Module\Message\Lib\PrivateMessageUiFactoryInterface;
use Stu\Orm\Entity\PrivateMessageFolder;
use Stu\Orm\Entity\PrivateMessage;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;

final class ShowWritePm implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'WRITE_PM';

    private const int CORRESPONDENCE_LIMIT_CLASSIC = 10;
    private const int CORRESPONDENCE_LIMIT_MESSENGER = PHP_INT_MAX;

    public function __construct(
        private readonly ShowWritePmRequestInterface $showWritePmRequest,
        private readonly ContactRepositoryInterface $contactRepository,
        private readonly PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository,
        private readonly PrivateMessageUiFactoryInterface $privateMessageUiFactory,
        private readonly PrivateMessageRepositoryInterface $privateMessageRepository,
        private readonly ComponentRegistrationInterface $componentRegistration,
        private readonly UserSettingsProviderInterface $userSettingsProvider
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $recipientId = $this->showWritePmRequest->getRecipientId();

        $pm = $this->privateMessageRepository->find($this->showWritePmRequest->getReplyPmId());
        if ($pm === null || $pm->getRecipient() !== $user) {
            $reply = null;
            $correspondence = null;
        } else {
            $reply = $pm;

            $isInboxMessengerStyle = $this->userSettingsProvider->isInboxMessengerStyle($user);

            $correspondence = array_map(
                fn(PrivateMessage $message): PrivateMessageListItem => new PrivateMessageListItem(
                    $this->privateMessageRepository,
                    $this->contactRepository,
                    $this->userSettingsProvider,
                    $message,
                    $game->getUser()
                ),
                $this->privateMessageRepository->getOrderedCorrepondence(
                    $reply->getRecipientId(),
                    $reply->getSenderId(),
                    [PrivateMessageFolderTypeEnum::SPECIAL_MAIN->value, PrivateMessageFolderTypeEnum::DEFAULT_OWN->value],
                    $isInboxMessengerStyle ? self::CORRESPONDENCE_LIMIT_MESSENGER : self::CORRESPONDENCE_LIMIT_CLASSIC
                )
            );
        }

        $game->setViewTemplate('html/message/writePm.twig');
        $game->setPageTitle('Neue private Nachricht');
        $game->appendNavigationPart(
            sprintf('pm.php?%s=1', self::VIEW_IDENTIFIER),
            'Private Nachricht verfassen'
        );
        $game->setTemplateVar(
            'RECIPIENT_ID',
            $recipientId === 0 ? '' : $recipientId
        );
        $game->setTemplateVar('REPLY', $reply);
        $game->setTemplateVar('CONTACT_LIST', $this->contactRepository->getOrderedByUser($user));
        $game->setTemplateVar('CORRESPONDENCE', $correspondence);
        $game->setTemplateVar(
            'PM_CATEGORIES',
            array_map(
                fn(PrivateMessageFolder $privateMessageFolder): PrivateMessageFolderItem =>
                $this->privateMessageUiFactory->createPrivateMessageFolderItem($privateMessageFolder),
                $this->privateMessageFolderRepository->getOrderedByUser($user)
            )
        );

        $this->componentRegistration->addComponentUpdate(GameComponentEnum::PM);
        $game->addExecuteJS("initTranslations();", GameEnum::JS_EXECUTION_AFTER_RENDER);
    }
}
