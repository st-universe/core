<?php

namespace Stu\Component\Cache;

use Cache\Adapter\Redis\RedisCachePool;
use Exception;
use Override;
use Psr\Cache\CacheItemPoolInterface;
use Redis;
use Stu\Module\Config\StuConfigInterface;

final class CacheProvider implements CacheProviderInterface
{
    public function __construct(private StuConfigInterface $config)
    {
    }

    #[Override]
    public function getRedisCachePool(): CacheItemPoolInterface
    {
        $redis = new Redis();

        $cacheSettings = $this->config->getCacheSettings();

        if ($cacheSettings->getRedisSocket() !== null) {
            try {
                $redis->connect($cacheSettings->getRedisSocket());
            } catch (Exception) {
                $redis->connect(
                    $cacheSettings->getRedisHost(),
                    $cacheSettings->getRedisPort()
                );
            }
        } else {
            $redis->connect(
                $cacheSettings->getRedisHost(),
                $cacheSettings->getRedisPort()
            );
        }
        $redis->setOption(Redis::OPT_PREFIX, $this->config->getDbSettings()->getDatabase());

        return new RedisCachePool($redis);
    }
}
