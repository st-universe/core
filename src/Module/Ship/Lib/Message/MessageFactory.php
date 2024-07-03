<?php

namespace Stu\Module\Ship\Lib\Message;

use Override;
use Stu\Module\PlayerSetting\Lib\UserEnum;

class MessageFactory implements MessageFactoryInterface
{
    #[Override]
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

    #[Override]
    public function createMessageCollection(): MessageCollectionInterface
    {
        return new MessageCollection();
    }
}
