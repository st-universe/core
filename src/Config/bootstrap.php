<?php

declare(strict_types=1);

namespace Stu\Config;

use DI\ContainerBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Setup;
use Noodlehaus\Config;
use Noodlehaus\ConfigInterface;
use Psr\Container\ContainerInterface;
use Stu\Control\AllianceController;
use Stu\Control\ColonyController;
use Stu\Control\ColonyListController;
use Stu\Control\CommController;
use Stu\Control\CrewController;
use Stu\Control\IndexController;
use Stu\Control\LogoutController;
use Stu\Control\ShipController;
use Stu\Control\ShiplistController;
use Stu\Control\StarmapController;
use Stu\Lib\Db;
use Stu\Lib\DbInterface;
use Stu\Lib\Session;
use Stu\Lib\SessionInterface;
use function DI\autowire;
use function DI\create;
use function DI\get;

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
    DbInterface::class => create(Db::class)
        ->constructor(
            get(ConfigInterface::class)
        ),
    SessionInterface::class => autowire(Session::class),
    EntityManagerInterface::class => function (ContainerInterface $c): EntityManagerInterface {
        $config = $c->get(ConfigInterface::class);

        $manager = EntityManager::create(
            [
                'driver' => 'mysqli',
                'user' => $config->get('db.user'),
                'password' => $config->get('db.pass'),
                'dbname'=> $config->get('db.database'),
                'host'  => $config->get('db.host'),
                'charset' => 'utf8',
            ],
            Setup::createAnnotationMetadataConfiguration(
                [__DIR__.'/../Orm/Entity/'],
                $config->get('debug.debug_mode')
            )
        );

        $manager->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'integer');
        return $manager;
    },
]);

$builder->addDefinitions([
    AllianceController::class => autowire(AllianceController::class),
    ColonyController::class => autowire(ColonyController::class),
    ColonyListController::class => autowire(ColonyListController::class),
    CommController::class => autowire(CommController::class),
    CrewController::class => autowire(CrewController::class),
    IndexController::class => autowire(IndexController::class),
    LogoutController::class => autowire(LogoutController::class),
    ShipController::class => autowire(ShipController::class),
    ShiplistController::class => autowire(ShiplistController::class),
    StarmapController::class => autowire(StarmapController::class),
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

$builder->addDefinitions([
    'maintenance_handler' => require_once __DIR__ . '/../Module/Maintenance/services.php',
]);

$builder->addDefinitions(
    require_once __DIR__.'/../Orm/Repository/services.php',
);
return $builder->build();
