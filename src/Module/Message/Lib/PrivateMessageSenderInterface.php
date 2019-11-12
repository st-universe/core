<?php

namespace Stu\Module\Message\Lib;

use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;

interface PrivateMessageSenderInterface
{
    public function send(
        int $senderId,
        int $recipientId,
        string $text,
        int $category = PrivateMessageFolderSpecialEnum::PM_SPECIAL_MAIN
    ): void;
}
