<?php

declare(strict_types=1);

namespace Stu\Config;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Logging\Middleware;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Hackzilla\PasswordGenerator\Generator\ComputerPasswordGenerator;
use Hackzilla\PasswordGenerator\Generator\PasswordGeneratorInterface;
use JBBCode\Parser;
use JsonMapper\JsonMapperFactory;
use JsonMapper\JsonMapperInterface;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;
use Monolog\Logger;
use Noodlehaus\Config;
use Noodlehaus\ConfigInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Stu\Component\Cache\CacheProvider;
use Stu\Component\Cache\CacheProviderInterface;
use Stu\Component\Logging\Sql\SqlLogger;
use Stu\Lib\ParserWithImage;
use Stu\Lib\ParserWithImageInterface;
use Stu\Lib\Session;
use Stu\Lib\SessionInterface;
use Stu\Lib\StuBbCodeDefinitionSet;
use Stu\Lib\StuBbCodeWithImageDefinitionSet;
use Stu\Module\Config\StuConfig;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\GameController;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Tal\TalPage;
use Stu\Module\Tal\TalPageInterface;
use Ubench;

use function DI\autowire;

return [
    ErrorHandler::class => autowire(),
    ConfigInterface::class => function (): ConfigInterface {
        $path = __DIR__ . '/../../';
        return new Config(
            [
                sprintf('%s/config.dist.json', $path),
                sprintf('?%s/config.json', $path),
            ]
        );
    },
    StuConfigInterface::class => autowire(StuConfig::class),
    CacheProviderInterface::class => autowire(CacheProvider::class),
    CacheItemPoolInterface::class => function (ContainerInterface $c): CacheItemPoolInterface {
        $stuConfig = $c->get(StuConfigInterface::class);

        if ($stuConfig->getCacheSettings()->useRedis()) {
            $cacheProvider = $c->get(CacheProviderInterface::class);

            return $cacheProvider->getRedisCachePool();
        } else {
            return new ArrayCachePool();
        }
    },
    SessionInterface::class => autowire(Session::class),
    EntityManagerInterface::class => function (ContainerInterface $c): EntityManagerInterface {
        $stuConfig = $c->get(StuConfigInterface::class);

        $emConfig = ORMSetup::createAttributeMetadataConfiguration(
            [__DIR__ . '/../Orm/Entity/'],
            $stuConfig->getDebugSettings()->isDebugMode(),
            __DIR__ . '/../OrmProxy/',
            $c->get(CacheItemPoolInterface::class)
        );
        $emConfig->setAutoGenerateProxyClasses(0);
        $emConfig->setProxyNamespace($stuConfig->getDbSettings()->getProxyNamespace());

        $manager = new EntityManager(
            $c->get(Connection::class),
            $emConfig
        );

        $manager->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'integer');
        return $manager;
    },
    SqlLogger::class => function (StuConfigInterface $stuConfig): SqlLogger {
        $logger = new Logger(
            'SqlLogger',
            [
                new RotatingFileHandler(
                    sprintf(
                        '%s/sql.log',
                        $stuConfig->getDebugSettings()->getSqlLoggingSettings()->getLogDirectory(),
                    ),
                    10,
                    Level::Info,
                ),
            ]
        );

        return new SqlLogger(
            $logger
        );
    },
    Connection::class => function (ContainerInterface $c): Connection {
        $config = $c->get(ConfigInterface::class);
        $stuConfig = $c->get(StuConfigInterface::class);

        //use sqlite database
        if ($stuConfig->getDbSettings()->useSqlite()) {
            $dsnParser = new DsnParser(['sqlite' => 'pdo_sqlite']);
            $connectionParams = $dsnParser
                ->parse('sqlite:///:memory:');

            return DriverManager::getConnection($connectionParams);
        }

        $configuration = null;
        if ($stuConfig->getDebugSettings()->getSqlLoggingSettings()->isActive()) {
            $logger = $c->get(SqlLogger::class);
            $configuration = new Configuration();
            $configuration->setMiddlewares([new Middleware($logger)]);
        }

        return DriverManager::getConnection([
            'driver' => 'pdo_pgsql',
            'user' => $config->get('db.user'),
            'password' => $config->get('db.pass'),
            'dbname' => $config->get('db.database'),
            'host'  => $config->get('db.host'),
            'charset' => 'utf8',
        ], $configuration);
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
    JsonMapperInterface::class => fn (): JsonMapperInterface => (new JsonMapperFactory())->bestFit(),
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
