<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider\Message;

use Override;
use Stu\Component\Player\Settings\UserSettingsProviderInterface;
use Stu\Module\Message\Lib\PrivateMessageListItem;
use Stu\Orm\Entity\PrivateMessage;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;

final class Conversation extends PrivateMessageListItem
{
    private const int MAX_PREVIEW_CHARS = 200;

    public function __construct(
        private PrivateMessage $message,
        private int $unreadPmCount,
        private string $dateString,
        User $currentUser,
        PrivateMessageRepositoryInterface $privateMessageRepository,
        ContactRepositoryInterface $contactRepository,
        UserSettingsProviderInterface $userSettingsProvider
    ) {
        parent::__construct(
            $privateMessageRepository,
            $contactRepository,
            $userSettingsProvider,
            $message,
            $currentUser
        );
    }

    #[Override]
    public function isMarkableAsNew(): bool
    {
        return $this->message->getNew();
    }

    public function getUnreadMessageCount(): int
    {
        return $this->unreadPmCount;
    }

    public function getLastHeadline(): string
    {
        $isCutOff = strlen($this->message->getText()) > self::MAX_PREVIEW_CHARS;

        return sprintf(
            '%s%s%s',
            $this->message->getInboxPm() === null ? sprintf(
                '%s: ',
                $this->getOtherUser()->getName()
            ) : '',
            substr($this->message->getText(), 0, self::MAX_PREVIEW_CHARS),
            $isCutOff ? '...' : ''
        );
    }

    public function getOtherUser(): User
    {
        return $this->message->getSender();
    }

    public function getDateString(): string
    {
        return $this->dateString;
    }
}
