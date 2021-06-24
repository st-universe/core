<?php

namespace Stu\Module\Message\Lib;

interface PrivateMessageSenderInterface
{
    public function send(
        int $senderId,
        int $recipientId,
        string $text,
        int $category = PrivateMessageFolderSpecialEnum::PM_SPECIAL_SYSTEM,
        string $href = null
    ): void;
}
