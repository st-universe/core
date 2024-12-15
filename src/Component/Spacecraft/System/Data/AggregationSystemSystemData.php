<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Data;

use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;

class AggregationSystemSystemData extends AbstractSystemData
{
    public int $commodityId = 0;


    #[Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::SYSTEM_AGGREGATION_SYSTEM;
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
