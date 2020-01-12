<?php

declare(strict_types=1);

namespace Stu\Module\Queue;

use Enqueue\AmqpLib\AmqpConnectionFactory;
use Interop\Amqp\AmqpContext;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Noodlehaus\ConfigInterface;
use Psr\Container\ContainerInterface;
use Seld\Signal\SignalHandler;
use Stu\Component\Queue\Consumer\DelayedBuildingJobConsumer;
use Stu\Component\Queue\Consumer\DelayedBuildingJobConsumerInterface;
use Stu\Component\Queue\Driver\DelayedJobDriver;
use Stu\Component\Queue\Driver\DelayedJobDriverInterface;
use Stu\Component\Queue\Message\MessageFactory;
use Stu\Component\Queue\Message\MessageFactoryInterface;
use Stu\Component\Queue\Message\MessageTransformator;
use Stu\Component\Queue\Message\MessageTransformatorInterface;
use Stu\Component\Queue\Publisher\DelayedJobPublisher;
use Stu\Component\Queue\Publisher\DelayedJobPublisherInterface;
use function DI\autowire;

return [
    AmqpContext::class => function (ContainerInterface $c): AmqpContext {
        $config = $c->get(ConfigInterface::class);

        $factory = new AmqpConnectionFactory([
            'host' => $config->get('mq.host'),
            'port' => $config->get('mq.port'),
            'user' => $config->get('mq.user'),
            'pass' => $config->get('mq.pass'),
            'vhost' => '/',
            'persisted' => true,
        ]);

        return $factory->createContext();
    },
    DelayedJobDriverInterface::class => function(ContainerInterface $c): DelayedJobDriverInterface {
        $config = $c->get(ConfigInterface::class);

        $path = sprintf(
            '%s/delayed_job_driver.log',
            $config->get('mq.debug.log_path')
        );

        $logger = new Logger('DelayedJobDriverLogger');
        $logger->pushHandler(
            new StreamHandler($path, $config->get('mq.debug.log_level')),

        );
        return new DelayedJobDriver(
            $c->get(AmqpContext::class),
            $c->get(MessageTransformatorInterface::class),
            SignalHandler::create(),
            $logger
        );
    },
    DelayedJobPublisherInterface::class => autowire(DelayedJobPublisher::class),
    DelayedBuildingJobConsumerInterface::class => autowire(DelayedBuildingJobConsumer::class),
    MessageTransformatorInterface::class => autowire(MessageTransformator::class),
    MessageFactoryInterface::class => autowire(MessageFactory::class),
];
