<?php

namespace Stu\Module\Commodity\Lib;

use Stu\Orm\Entity\Commodity;

interface CommodityCacheInterface
{
    public function get(int $commodityId): Commodity;

    /** @return array<Commodity> */
    public function getAll(?int $type = null): array;
}
