<?php

declare(strict_types=1);

namespace Stu\Module\Commodity;

use Stu\Module\Commodity\Lib\CommodityCache;
use Stu\Module\Commodity\Lib\CommodityCacheInterface;

use function DI\autowire;

return [
    CommodityCacheInterface::class => autowire(CommodityCache::class)
];
