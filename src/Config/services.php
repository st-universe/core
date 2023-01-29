<?php

declare(strict_types=1);

namespace Stu\Config;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Cache\Adapter\Redis\RedisCachePool;
use Cache\Bridge\Doctrine\DoctrineCacheBridge;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Hackzilla\PasswordGenerator\Generator\ComputerPasswordGenerator;
use Hackzilla\PasswordGenerator\Generator\PasswordGeneratorInterface;
use JBBCode\Parser;
use JsonMapper\JsonMapperFactory;
use JsonMapper\JsonMapperInterface;
use Noodlehaus\Config;
use Noodlehaus\ConfigInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Redis;
use Stu\Lib\ParserWithImage;
use Stu\Lib\ParserWithImageInterface;
use Stu\Lib\Session;
use Stu\Lib\SessionInterface;
use Stu\Lib\StuBbCodeDefinitionSet;
use Stu\Lib\StuBbCodeWithImageDefinitionSet;
use Stu\Module\Control\EntityManagerCreator;
use Stu\Module\Control\EntityManagerCreatorInterface;
use Stu\Module\Control\EntityManagerLogging;
use Stu\Module\Control\EntityManagerLoggingInterface;
use Stu\Module\Control\GameController;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Tal\TalPage;
use Stu\Module\Tal\TalPageInterface;
use Ubench;
use function DI\autowire;

return [
    ConfigInterface::class => function (): ConfigInterface {
        $path = __DIR__ . '/../../';
        return new Config(
            [
                sprintf('%s/config.dist.json', $path),
                sprintf('?%s/config.json', $path),
            ]
        );
    },
    CacheItemPoolInterface::class => function (ContainerInterface $c): CacheItemPoolInterface {
        $config = $c->get(ConfigInterface::class);

        if ($config->get('debug.debug_mode') === true) {
            return new ArrayCachePool();
        } else {
            $redis = new Redis();

            if ($config->has('cache.redis_socket')) {
                try {
                    $redis->connect($config->get('cache.redis_socket'));
                } catch (Exception $e) {
                    $redis->connect(
                        $config->get('cache.redis_host'),
                        $config->get('cache.redis_port')
                    );
                }
            } else {
                $redis->connect(
                    $config->get('cache.redis_host'),
                    $config->get('cache.redis_port')
                );
            }
            $redis->setOption(Redis::OPT_PREFIX, $config->get('db.database'));

            return new RedisCachePool($redis);
        }
    },
    SessionInterface::class => autowire(Session::class),
    EntityManagerCreatorInterface::class => autowire(EntityManagerCreator::class),
    EntityManagerInterface::class => function (ContainerInterface $c): EntityManagerInterface {
        $config = $c->get(ConfigInterface::class);
        $cacheDriver = new DoctrineCacheBridge($c->get(CacheItemPoolInterface::class));

        $emConfig = new Configuration();
        $emConfig->setAutoGenerateProxyClasses(0);
        $emConfig->setMetadataCacheImpl($cacheDriver);
        $emConfig->setQueryCacheImpl($cacheDriver);

        $driverImpl = $emConfig->newDefaultAnnotationDriver(__DIR__ . '/../Orm/Entity/');
        $emConfig->setMetadataDriverImpl($driverImpl);
        $emConfig->setProxyDir(sprintf(
            '%s/../OrmProxy/',
            __DIR__
        ));
        $emConfig->setProxyNamespace($config->get('db.proxy_namespace'));

        $manager = EntityManager::create(
            [
                'driver' => 'pdo_pgsql',
                'user' => $config->get('db.user'),
                'password' => $config->get('db.pass'),
                'dbname' => $config->get('db.database'),
                'host'  => $config->get('db.host'),
                'charset' => 'utf8',
            ],
            $emConfig
        );

        $manager->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'integer');
        return $manager;
    },
    EntityManagerLoggingInterface::class => function (ContainerInterface $c): EntityManagerLogging {
        $entityManagerCreator = $c->get(EntityManagerCreatorInterface::class);

        $entityManager = $entityManagerCreator->create();
        return new EntityManagerLogging($entityManager);
    },
    TalPageInterface::class => autowire(TalPage::class),
    GameControllerInterface::class => autowire(GameController::class),
    Parser::class => function (): Parser {
        $parser = new Parser();
        $parser->addCodeDefinitionSet(new StuBbCodeDefinitionSet());
        return $parser;
    },
    ParserWithImageInterface::class => function (): ParserWithImage {
        $parser = new Parser();
        $parser->addCodeDefinitionSet(new StuBbCodeWithImageDefinitionSet());
        return new ParserWithImage($parser);
    },
    JsonMapperInterface::class => function (): JsonMapperInterface {
        return (new JsonMapperFactory())->bestFit();
    },
    Ubench::class => function (): Ubench {
        $bench = new Ubench();
        $bench->start();

        return $bench;
    },
    PasswordGeneratorInterface::class => function (): PasswordGeneratorInterface {
        $generator = new ComputerPasswordGenerator();

        $generator
            ->setOptionValue(ComputerPasswordGenerator::OPTION_UPPER_CASE, true)
            ->setOptionValue(ComputerPasswordGenerator::OPTION_LOWER_CASE, true)
            ->setOptionValue(ComputerPasswordGenerator::OPTION_NUMBERS, true)
            ->setOptionValue(ComputerPasswordGenerator::OPTION_SYMBOLS, false)
            ->setOptionValue(ComputerPasswordGenerator::OPTION_LENGTH, 10);

        return $generator;
    },
];
