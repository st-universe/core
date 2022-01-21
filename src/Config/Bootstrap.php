<?php

declare(strict_types=1);

namespace Stu\Config;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Cache\Adapter\Redis\RedisCachePool;
use Cache\Bridge\Doctrine\DoctrineCacheBridge;
use Curl\Curl;
use DI\ContainerBuilder;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Hackzilla\PasswordGenerator\Generator\ComputerPasswordGenerator;
use Hackzilla\PasswordGenerator\Generator\PasswordGeneratorInterface;
use JBBCode\Parser;
use Noodlehaus\Config;
use Noodlehaus\ConfigInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Redis;
use Stu\Lib\ParserWithImageInterface;
use Stu\Lib\StuBbCodeDefinitionSet;
use Stu\Module\Control\GameController;
use Stu\Module\Control\GameControllerInterface;
use Stu\Lib\Session;
use Stu\Lib\SessionInterface;
use Stu\Lib\StuBbCodeWithImageDefinitionSet;
use Stu\Module\Tal\TalPage;
use Stu\Module\Tal\TalPageInterface;
use Ubench;
use Usox\IpIntel\IpIntel;
use Usox\IpIntel\IpIntelInterface;
use function DI\autowire;

require_once __DIR__ . '/../../vendor/autoload.php';

$builder = new ContainerBuilder();

$builder->addDefinitions([
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
    TalPageInterface::class => autowire(TalPage::class),
    GameControllerInterface::class => autowire(GameController::class),
    Parser::class => function (): Parser {
        $parser = new Parser();
        $parser->addCodeDefinitionSet(new StuBbCodeDefinitionSet());
        return $parser;
    },
    ParserWithImageInterface::class => function (): Parser {
        $parser = new Parser();
        $parser->addCodeDefinitionSet(new StuBbCodeWithImageDefinitionSet());
        return $parser;
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
    IpIntelInterface::class => function (ContainerInterface $c): IpIntelInterface {
        return new IpIntel(
            new Curl(),
            $c->get(ConfigInterface::class)->get('security.validation.ip_intel_email_address'),
            null,
            2
        );
    },
]);

$builder->addDefinitions(
    require_once __DIR__ . '/../Module/Database/services.php'
);
$builder->addDefinitions(
    require_once __DIR__ . '/../Module/Research/services.php'
);
$builder->addDefinitions(
    require_once __DIR__ . '/../Module/Maindesk/services.php'
);
$builder->addDefinitions(
    require_once __DIR__ . '/../Module/Crew/services.php'
);
$builder->addDefinitions(
    require_once __DIR__ . '/../Module/Notes/services.php'
);
$builder->addDefinitions(
    require_once __DIR__ . '/../Module/History/services.php'
);
$builder->addDefinitions(
    require_once __DIR__ . '/../Module/PlayerProfile/services.php'
);
$builder->addDefinitions(
    require_once __DIR__ . '/../Module/Trade/services.php'
);
$builder->addDefinitions(
    require_once __DIR__ . '/../Module/PlayerSetting/services.php'
);
$builder->addDefinitions(
    require_once __DIR__ . '/../Module/Ship/services.php'
);
$builder->addDefinitions(
    require_once __DIR__ . '/../Module/Station/services.php'
);
$builder->addDefinitions(
    require_once __DIR__ . '/../Module/Alliance/services.php'
);
$builder->addDefinitions(
    require_once __DIR__ . '/../Module/Colony/services.php'
);
$builder->addDefinitions(
    require_once __DIR__ . '/../Module/Starmap/services.php'
);
$builder->addDefinitions(
    require_once __DIR__ . '/../Module/Index/services.php'
);
$builder->addDefinitions(
    require_once __DIR__ . '/../Module/Building/services.php'
);
$builder->addDefinitions(
    require_once __DIR__ . '/../Module/Communication/services.php'
);
$builder->addDefinitions(
    require_once __DIR__ . '/../Module/Admin/services.php'
);
$builder->addDefinitions(
    require_once __DIR__ . '/../Module/Control/services.php'
);
$builder->addDefinitions(
    require_once __DIR__ . '/../Module/Api/services.php'
);
$builder->addDefinitions(
    require_once __DIR__ . '/../Module/Message/services.php'
);
$builder->addDefinitions(
    require_once __DIR__ . '/../Component/Player/services.php'
);
$builder->addDefinitions(
    require_once __DIR__ . '/../Component/Ship/services.php'
);
$builder->addDefinitions(
    require_once __DIR__ . '/../Component/Station/services.php'
);
$builder->addDefinitions(
    require_once __DIR__ . '/../Module/Logging/services.php'
);
$builder->addDefinitions(
    require_once __DIR__ . '/../Component/Admin/services.php'
);
$builder->addDefinitions(
    require_once __DIR__ . '/../Component/Communication/services.php'
);
$builder->addDefinitions(
    require_once __DIR__ . '/../Component/Building/services.php'
);
$builder->addDefinitions(
    require_once __DIR__ . '/../Component/Colony/services.php'
);
$builder->addDefinitions(
    require_once __DIR__ . '/../Component/Queue/services.php'
);
$builder->addDefinitions(
    require_once __DIR__ . '/../Component/Process/services.php'
);

$builder->addDefinitions([
    'maintenance_handler' => require_once __DIR__ . '/../Module/Maintenance/services.php',
]);
$builder->addDefinitions(
    require_once __DIR__ . '/../Module/Tick/services.php'
);

$builder->addDefinitions(
    require_once __DIR__ . '/../Orm/Repository/services.php',
);

/**
 * @var ContainerInterface $container
 */
$container = $builder->build();

require_once __DIR__ . '/../Config/ErrorHandler.php';

$config = $container->get(ConfigInterface::class);

ini_set('date.timezone', 'Europe/Berlin');
set_include_path(get_include_path() . PATH_SEPARATOR . $config->get('game.webroot'));

require_once __DIR__ . '/TalesRegistry.php';
