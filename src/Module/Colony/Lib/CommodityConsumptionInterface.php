<?php

namespace Stu\Module\Colony\Lib;

use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\CommodityInterface;

interface CommodityConsumptionInterface
{
    /**
     * @param array<ColonyProduction> $production
     *
     * @return array<int, array{turnsleft: int, commodity: CommodityInterface}>
     */
    public function getConsumption(
        array $production,
        ColonyInterface $colony
    ): array;
}
