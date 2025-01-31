<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Data;

use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;

class BussardCollectorSystemData extends AbstractSystemData
{
    public int $commodityId = 0;


    #[Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::BUSSARD_COLLECTOR;
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
