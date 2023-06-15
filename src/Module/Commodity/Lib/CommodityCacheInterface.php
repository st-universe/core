<?php

namespace Stu\Module\Commodity\Lib;

use Stu\Orm\Entity\CommodityInterface;

interface CommodityCacheInterface
{
    public function get(int $commodityId): CommodityInterface;
}
