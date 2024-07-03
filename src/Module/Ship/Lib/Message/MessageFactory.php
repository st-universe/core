<?php

namespace Stu\Module\Ship\Lib\Message;

use Stu\Module\PlayerSetting\Lib\UserEnum;

class MessageFactory implements MessageFactoryInterface
{
    public function createMessage(
        ?int $senderId = null,
        ?int $recipientId = null,
        array $msg = []
    ): MessageInterface {

        return new Message(
            $senderId ?? UserEnum::USER_NOONE,
            $recipientId,
            $msg
        );
    }

    public function createMessageCollection(): MessageCollectionInterface
    {
        return new MessageCollection();
    }
}
