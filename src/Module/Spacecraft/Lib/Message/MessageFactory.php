<?php

namespace Stu\Module\Spacecraft\Lib\Message;

use Stu\Module\PlayerSetting\Lib\UserConstants;

class MessageFactory implements MessageFactoryInterface
{
    #[\Override]
    public function createMessage(
        ?int $senderId = null,
        ?int $recipientId = null,
        array $msg = []
    ): MessageInterface {

        return new Message(
            $senderId ?? UserConstants::USER_NOONE,
            $recipientId,
            $msg
        );
    }

    #[\Override]
    public function createMessageCollection(): MessageCollectionInterface
    {
        return new MessageCollection($this);
    }
}
