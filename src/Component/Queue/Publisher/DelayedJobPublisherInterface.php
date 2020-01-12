<?php

namespace Stu\Component\Queue\Publisher;

use Stu\Component\Queue\Message\TransformableMessageInterface;

interface DelayedJobPublisherInterface
{
    public function publish(
        TransformableMessageInterface $message,
        int $delay
    ): void;
}
