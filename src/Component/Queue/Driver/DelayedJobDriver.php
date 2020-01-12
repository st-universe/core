<?php

declare(strict_types=1);

namespace Stu\Component\Queue\Driver;

use Exception;
use Interop\Amqp\AmqpContext;
use Interop\Amqp\AmqpQueue;
use Interop\Amqp\Impl\AmqpBind;
use Interop\Amqp\Impl\AmqpTopic;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Seld\Signal\SignalHandler;
use Stu\Component\Queue\Message\MessageTransformatorInterface;

final class DelayedJobDriver implements DelayedJobDriverInterface
{
    private AmqpContext $amqpContext;

    private MessageTransformatorInterface $messageTransformator;

    private SignalHandler $signalHandler;

    private ?LoggerInterface $logger;

    public function __construct(
        AmqpContext $amqpContext,
        MessageTransformatorInterface $messageTransformator,
        SignalHandler $signalHandler,
        LoggerInterface $logger = null
    ) {
        $this->amqpContext = $amqpContext;
        $this->messageTransformator = $messageTransformator;
        $this->signalHandler = $signalHandler;
        $this->logger = $logger;
    }

    public function declareTopic(): AmqpTopic
    {
        /** @var AmqpTopic $topic */
        $topic = $this->amqpContext->createTopic('delayed_job');
        $topic->setType('x-delayed-message');
        $topic->setArguments([
            'x-delayed-type' => 'direct'
        ]);

        $this->amqpContext->declareTopic($topic);

        return $topic;
    }

    public function declareQueue(
        string $queueName,
        ?string $route = null
    ): AmqpQueue {
        $queue = $this->amqpContext->createQueue($queueName);
        $queue->addFlag(AmqpQueue::FLAG_DURABLE);
        $this->amqpContext->declareQueue($queue);

        $this->amqpContext->bind(
            new AmqpBind($this->declareTopic(), $queue, $route)
        );

        return $queue;
    }

    public function publish(
        string $message,
        int $delay,
        ?string $route = null
    ): void {
        $this->log(
            Logger::INFO,
            'Publish',
            ['message' => $message]
        );

        $message = $this->amqpContext->createMessage($message);
        $message->setRoutingKey($route);
        $message->setProperties([
            'x-delay' => $delay * 1000
        ]);

        $this->amqpContext->createProducer()
            ->send(
                $this->declareTopic(),
                $message
            );
    }

    public function consume(
        string $queueName,
        string $consumerName,
        callable $processor,
        ?string $route = null,
        int $duration = 0
    ): void {
        $this->log(
            Logger::INFO,
            'Start consumer'
        );
        $queue = $this->declareQueue(
            $queueName,
            $route
        );

        $consumer = $this->amqpContext->createConsumer($queue);
        $consumer->setConsumerTag($consumerName);

        while (true) {
            if ($this->signalHandler->isTriggered()) {
                break;
            }

            $message = $consumer->receiveNoWait();

            if ($message === null) {
                $this->log(
                    Logger::DEBUG,
                    'No message received, retry'
                );

                usleep(100000); //100ms

                continue;
            }
            $this->log(
                Logger::INFO,
                'Consume',
                ['message' => $message->getBody()]
            );

            try {
                $processor($this->messageTransformator->decode($message));
            } catch (Exception $e) {
                $this->log(
                    Logger::ERROR,
                    'Error occured',
                    ['message' => $e->getTrace()]
                );
            }

            $consumer->acknowledge($message);
        }

        $this->log(
            Logger::INFO,
            'Stop consumer'
        );
    }

    private function log(
        int $level,
        string $message,
        array $context = []
    ): void {
        if ($this->logger === null) {
            return;
        }

        $this->logger->log(
            $level,
            $message,
            $context
        );
    }
}
