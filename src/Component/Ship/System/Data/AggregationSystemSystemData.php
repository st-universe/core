<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Data;

use Override;
use Stu\Component\Ship\System\ShipSystemTypeEnum;

class AggregationSystemSystemData extends AbstractSystemData
{
    public int $commodityId = 0;


    #[Override]
    public function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_AGGREGATION_SYSTEM;
    }

    public function getCommodityId(): int
    {
        return $this->commodityId;
    }

    public function setCommodityId(int $commodityId): AggregationSystemSystemData
    {
        $this->commodityId = $commodityId;
        return $this;
    }
}
