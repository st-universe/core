<?php

declare(strict_types=1);

namespace Stu\Component\Queue\Publisher;

use Interop\Amqp\AmqpContext;
use Stu\Component\Queue\Driver\DelayedJobDriverInterface;
use Stu\Component\Queue\Message\MessageTransformatorInterface;
use Stu\Component\Queue\Message\TransformableMessageInterface;
use Stu\Component\Queue\QueueRouteEnum;

final class DelayedJobPublisher implements DelayedJobPublisherInterface
{
    private DelayedJobDriverInterface $delayedJobDriver;

    private AmqpContext $amqpContext;

    private MessageTransformatorInterface $messageTransformator;

    public function __construct(
        DelayedJobDriverInterface $delayedJobDriver,
        AmqpContext $amqpContext,
        MessageTransformatorInterface $messageTransformator
    ) {
        $this->delayedJobDriver = $delayedJobDriver;
        $this->amqpContext = $amqpContext;
        $this->messageTransformator = $messageTransformator;
    }

    public function publish(
        TransformableMessageInterface $message,
        int $delay
    ): void {
        $this->delayedJobDriver->publish(
            $this->messageTransformator->encode($message),
            $delay,
            QueueRouteEnum::DELAYED_BUILDING_JOB
        );
    }
}
