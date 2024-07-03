<?php

namespace Stu\Module\Ship\Lib\Message;

interface MessageFactoryInterface
{
    /**
     * @param array<string> $msg
     */
    public function createMessage(
        ?int $senderId = null,
        ?int $recipientId = null,
        array $msg = []
    ): MessageInterface;

    public function createMessageCollection(): MessageCollectionInterface;
}
