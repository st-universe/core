<?php

declare(strict_types=1);

namespace Stu\Component\Colony\Commodity;

use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Module\Commodity\CommodityTypeEnum;

final class ColonyProductionSumReducer implements ColonyProductionSumReducerInterface
{

    /**
     * @param array<int, ColonyProduction> $production
     */
    public function reduce(
        array $production
    ): int {

        return array_reduce(
            array_filter(
                $production,
                fn (ColonyProduction $item): bool => $item->getCommodityType() !== CommodityTypeEnum::COMMODITY_TYPE_EFFECT
            ),
            fn (int $value, ColonyProduction $item): int => $value + $item->getProduction(),
            0
        );
    }
}
