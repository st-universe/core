<?php

namespace Stu\Module\Message\Lib;

use Stu\Lib\General\EntityWithHrefInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Orm\Entity\User;

interface PrivateMessageSenderInterface
{
    public function send(
        int $senderId,
        int $recipientId,
        string|InformationWrapper $information,
        PrivateMessageFolderTypeEnum $folderType = PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM,
        null|string|EntityWithHrefInterface $href = null,
        bool $isRead = false
    ): void;

    /**
     * @param User[] $recipients
     */
    public function sendBroadcast(
        User $sender,
        array $recipients,
        string $text
    ): void;
}
