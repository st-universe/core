<?php

declare(strict_types=1);

namespace Stu\Component\Queue\Message;

use Interop\Queue\Message;
use Stu\Component\Queue\Message\Type\MessageTypeEnum;

final class MessageTransformator implements MessageTransformatorInterface
{
    public function decode(Message $message): TransformableMessageInterface
    {
        $data = json_decode($message->getBody(), true);

        $type = MessageTypeEnum::map($data['typeId']);
        $type->unserialize($data['message']);

        return $type;
    }

    public function encode(TransformableMessageInterface $serializable): string
    {
        return json_encode([
            'typeId' => $serializable->getId(),
            'message' => $serializable->serialize()
        ]);
    }
}
