<?php

namespace Stu\Module\Control;

use Cache\Bridge\Doctrine\DoctrineCacheBridge;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Noodlehaus\ConfigInterface;
use Psr\Cache\CacheItemPoolInterface;

final class EntityManagerCreator implements EntityManagerCreatorInterface
{
    private ConfigInterface $config;

    private CacheItemPoolInterface $cacheItemPool;

    public function __construct(
        ConfigInterface $config,
        CacheItemPoolInterface $cacheItemPool
    ) {
        $this->config = $config;
        $this->cacheItemPool = $cacheItemPool;
    }

    public function create(): EntityManagerInterface
    {
        $config = $this->config;
        $cacheDriver = new DoctrineCacheBridge($this->cacheItemPool);

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
    }
}
