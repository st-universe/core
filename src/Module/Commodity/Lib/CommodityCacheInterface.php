<?php

namespace Stu\Module\Commodity\Lib;

use Stu\Orm\Entity\CommodityInterface;

interface CommodityCacheInterface
{
    public function get(int $commodityId): CommodityInterface;

    /** @return array<CommodityInterface> */
    public function getAll(int $type = null): array;
}
