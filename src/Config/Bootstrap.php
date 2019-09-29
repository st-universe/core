<?php

declare(strict_types=1);

namespace Stu\Config;

use DI\ContainerBuilder;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Proxy\AbstractProxyFactory;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Setup;
use JBBCode\Parser;
use Noodlehaus\Config;
use Noodlehaus\ConfigInterface;
use Psr\Container\ContainerInterface;
use Stu\Lib\StuBbCodeDefinitionSet;
use Stu\Module\Control\GameController;
use Stu\Module\Control\GameControllerInterface;
use Stu\Lib\Session;
use Stu\Lib\SessionInterface;
use Stu\Module\Tal\TalPage;
use Stu\Module\Tal\TalPageInterface;
use Ubench;
use function DI\autowire;

require_once __DIR__.'/../../vendor/autoload.php';

$builder = new ContainerBuilder();

$builder->addDefinitions([
    ConfigInterface::class => function (): ConfigInterface {
        $path = __DIR__.'/../../';
        return new Config(
            [
                sprintf('%s/config.dist.json', $path),
                sprintf('?%s/config.json', $path),
            ]
        );
    },
    SessionInterface::class => autowire(Session::class),
    EntityManagerInterface::class => function (ContainerInterface $c): EntityManagerInterface {
        $config = $c->get(ConfigInterface::class);

        $emConfig = new Configuration();
        $emConfig->setAutoGenerateProxyClasses(AbstractProxyFactory::AUTOGENERATE_NEVER);
        if ($config->get('debug.debug_mode') === true) {
            $emConfig->setMetadataCacheImpl(new ArrayCache());
        } else {
            $emConfig->setMetadataCacheImpl(new ArrayCache());
            //$emConfig->setMetadataCacheImpl(new ApcuCache());
        }
        $driverImpl = $emConfig->newDefaultAnnotationDriver(__DIR__ . '/../Orm/Entity/');
        $emConfig->setMetadataDriverImpl($driverImpl);
        $emConfig->setProxyDir('/tmp/');
        $emConfig->setProxyNamespace('Stu\Orm\Proxy');

        $manager = EntityManager::create(
            [
                'driver' => 'mysqli',
                'user' => $config->get('db.user'),
                'password' => $config->get('db.pass'),
                'dbname'=> $config->get('db.database'),
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
    Ubench::class => function (ContainerInterface $c): Ubench {
        $bench = new Ubench();
        $bench->start();

        return $bench;
    },
]);

$builder->addDefinitions(
    require_once __DIR__.'/../Module/Database/services.php'
);
$builder->addDefinitions(
    require_once __DIR__.'/../Module/Research/services.php'
);
$builder->addDefinitions(
    require_once __DIR__.'/../Module/Maindesk/services.php'
);
$builder->addDefinitions(
    require_once __DIR__.'/../Module/Crew/services.php'
);
$builder->addDefinitions(
    require_once __DIR__.'/../Module/Notes/services.php'
);
$builder->addDefinitions(
    require_once __DIR__.'/../Module/History/services.php'
);
$builder->addDefinitions(
    require_once __DIR__.'/../Module/PlayerProfile/services.php'
);
$builder->addDefinitions(
    require_once __DIR__.'/../Module/Trade/services.php'
);
$builder->addDefinitions(
    require_once __DIR__.'/../Module/PlayerSetting/services.php'
);
$builder->addDefinitions(
    require_once __DIR__.'/../Module/Ship/services.php'
);
$builder->addDefinitions(
    require_once __DIR__.'/../Module/Alliance/services.php'
);
$builder->addDefinitions(
    require_once __DIR__.'/../Module/Colony/services.php'
);
$builder->addDefinitions(
    require_once __DIR__.'/../Module/Starmap/services.php'
);
$builder->addDefinitions(
    require_once __DIR__.'/../Module/Index/services.php'
);
$builder->addDefinitions(
    require_once __DIR__.'/../Module/Building/services.php'
);
$builder->addDefinitions(
    require_once __DIR__.'/../Module/Communication/services.php'
);
$builder->addDefinitions(
    require_once __DIR__.'/../Module/Api/services.php'
);
$builder->addDefinitions(
    require_once __DIR__.'/../Component/Player/services.php'
);
$builder->addDefinitions(
    require_once __DIR__.'/../Component/Ship/services.php'
);

$builder->addDefinitions([
    'maintenance_handler' => require_once __DIR__ . '/../Module/Maintenance/services.php',
]);
$builder->addDefinitions(
    require_once __DIR__.'/../Module/Tick/services.php'
);

$builder->addDefinitions(
    require_once __DIR__.'/../Orm/Repository/services.php',
);

/**
 * @var ContainerInterface $container
 */
$container = $builder->build();

require_once __DIR__ . '/../Config/ErrorHandler.php';

$config = $container->get(ConfigInterface::class);

ini_set('date.timezone', 'Europe/Berlin');
set_include_path(get_include_path() . PATH_SEPARATOR . $config->get('game.webroot'));

require_once __DIR__ . '/../inc/func.inc.php';
include_once __DIR__ . '/../inc/generated/fieldtypesname.inc.php';
