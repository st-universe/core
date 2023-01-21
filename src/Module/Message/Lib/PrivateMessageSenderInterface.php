<?php

namespace Stu\Module\Message\Lib;

use Stu\Orm\Entity\UserInterface;

interface PrivateMessageSenderInterface
{
    public function send(
        int $senderId,
        int $recipientId,
        string $text,
        int $category = PrivateMessageFolderSpecialEnum::PM_SPECIAL_SYSTEM,
        string $href = null
    ): void;

    /**
     * @param UserInterface[] $recipients
     */
    public function sendBroadcast(
        UserInterface $sender,
        array $recipients,
        string $text
    ): void;
}
