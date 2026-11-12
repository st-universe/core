<?php

declare(strict_types=1);

namespace Stu\Orm\Transaction;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\ORM\ORMSetup;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Stu\Component\Borg\BorgSphereDeletionHandler;
use Stu\Component\Borg\BorgSphereDeletionListener;
use Stu\Component\Crew\CrewDeletionHandler;
use Stu\Component\Crew\CrewDeletionListener;
use Stu\Extension\ExtensionRegistry;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;

use function DI\autowire;
use function DI\get;

return [
    ConnectionFactoryInterface::class => autowire(ConnectionFactory::class)
        ->constructorParameter('extensions', get(ExtensionRegistry::class)),
    Connection::class => fn (ContainerInterface $c): Connection =>
        $c->get(ConnectionFactoryInterface::class)
            ->createConnection(),
    Configuration::class => function (ContainerInterface $c): Configuration {
        $stuConfig = $c->get(StuConfigInterface::class);

        $emConfig = ORMSetup::createAttributeMetadataConfig(
            [__DIR__ . '/../../Orm/Entity/', ...$c->get(ExtensionRegistry::class)->entityPaths()],
            $stuConfig->getDebugSettings()->isDebugMode(),
            (string)$stuConfig->getGameSettings()->getVersion(),
            $c->get(CacheItemPoolInterface::class)
        );
        $emConfig->enableNativeLazyObjects(true);
        $emConfig->setNamingStrategy(new UnderscoreNamingStrategy());

        return $emConfig;
    },
    BorgSphereDeletionListener::class => fn (ContainerInterface $c): BorgSphereDeletionListener => new BorgSphereDeletionListener(
        fn (): BorgSphereDeletionHandler => $c->get(BorgSphereDeletionHandler::class)
    ),
    EntityManagerFactoryInterface::class => autowire(EntityManagerFactory::class)
        ->constructorParameter('borgSphereDeletionListener', get(BorgSphereDeletionListener::class))
        ->constructorParameter('crewDeletionListener', get(CrewDeletionListener::class)),
    CrewDeletionListener::class => fn (ContainerInterface $c): CrewDeletionListener => new CrewDeletionListener(
        fn (): CrewDeletionHandler => $c->get(CrewDeletionHandler::class)
    ),
    EntityManagerInterface::class => fn (ContainerInterface $c): EntityManagerInterface => new ReopeningEntityManager(
        $c->get(EntityManagerFactoryInterface::class),
        $c->get(Configuration::class),
        $c->get(LoggerUtilFactoryInterface::class)
    )
];
