<?php

namespace Stu\Module\Cache;

use Psr\Cache\CacheItemPoolInterface;

interface CacheProviderInterface
{
    public function getRedisCachePool(): CacheItemPoolInterface;
}
