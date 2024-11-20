<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Data;

use Override;
use Stu\Component\Ship\System\ShipSystemTypeEnum;

class BussardCollectorSystemData extends AbstractSystemData
{
    public int $commodityId = 0;


    #[Override]
    public function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_BUSSARD_COLLECTOR;
    }

    public function getCommodityId(): int
    {
        return $this->commodityId;
    }

    public function setCommodityId(int $commodityId): BussardCollectorSystemData
    {
        $this->commodityId = $commodityId;
        return $this;
    }
}
