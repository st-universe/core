<?php

namespace Stu\Component\Cache;

use Psr\Cache\CacheItemPoolInterface;

interface CacheProviderInterface
{
    public function getRedisCachePool(): CacheItemPoolInterface;
}
