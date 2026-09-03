<?php

declare(strict_types=1);

namespace Stu\Component\Cache;

use Exception;
use Psr\Cache\CacheItemPoolInterface;
use Redis;
use Stu\Module\Config\StuConfigInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;

final class CacheProvider implements CacheProviderInterface
{
    public function __construct(private StuConfigInterface $config) {}

    #[\Override]
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

        return new RedisAdapter($redis);
    }
}
