<?php

namespace Stu\Module\Communication\Lib;

interface PrivateMessageSenderInterface
{
    public function send(
        int $senderId,
        int $recipientId,
        string $text,
        int $category = PM_SPECIAL_MAIN
    ): void;
}