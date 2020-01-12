<?php

namespace Stu\Component\Queue\Driver;

use Interop\Amqp\AmqpQueue;
use Interop\Amqp\Impl\AmqpTopic;

interface DelayedJobDriverInterface
{
    public function declareTopic(): AmqpTopic;

    public function declareQueue(
        string $queueName,
        ?string $route = null
    ): AmqpQueue;

    public function publish(
        string $message,
        int $delay,
        ?string $route = null
    ): void;

    public function consume(
        string $queueName,
        string $consumerName,
        callable $processor,
        ?string $route = null,
        int $duration = 0
    ): void;
}
