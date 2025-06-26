<?php

namespace Stu\Module\Colony\Lib;

use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Commodity;

interface CommodityConsumptionInterface
{
    /**
     * @param array<ColonyProduction> $production
     *
     * @return array<int, array{turnsleft: int, commodity: Commodity}>
     */
    public function getConsumption(
        array $production,
        Colony $colony
    ): array;
}
