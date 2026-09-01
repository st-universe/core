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
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;

use function DI\autowire;

return [
    ConnectionFactoryInterface::class => autowire(ConnectionFactory::class),
    Connection::class => function (ContainerInterface $c): Connection {

        return $c->get(ConnectionFactoryInterface::class)
            ->createConnection();
    },
    Configuration::class => function (ContainerInterface $c): Configuration {
        $stuConfig = $c->get(StuConfigInterface::class);

        $emConfig = ORMSetup::createAttributeMetadataConfig(
            [__DIR__ . '/../../Orm/Entity/'],
            $stuConfig->getDebugSettings()->isDebugMode(),
            (string)$stuConfig->getGameSettings()->getVersion(),
            $c->get(CacheItemPoolInterface::class)
        );
        $emConfig->enableNativeLazyObjects(true);
        $emConfig->setNamingStrategy(new UnderscoreNamingStrategy());

        return $emConfig;
    },
    EntityManagerFactoryInterface::class => autowire(EntityManagerFactory::class),
    EntityManagerInterface::class => function (ContainerInterface $c): EntityManagerInterface {

        return new ReopeningEntityManager(
            $c->get(EntityManagerFactoryInterface::class),
            $c->get(Configuration::class),
            $c->get(LoggerUtilFactoryInterface::class)
        );
    }
];
