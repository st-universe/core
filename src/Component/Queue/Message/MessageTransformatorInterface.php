<?php

namespace Stu\Component\Queue\Message;

use Interop\Queue\Message;

interface MessageTransformatorInterface
{
    public function decode(Message $message): TransformableMessageInterface;

    public function encode(TransformableMessageInterface $serializable): string;
}
