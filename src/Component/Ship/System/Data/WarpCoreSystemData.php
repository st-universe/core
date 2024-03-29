<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Data;

use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\ReactorWrapperInterface;

class WarpCoreSystemData extends AbstractReactorSystemData
{
    function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_WARPCORE;
    }

    public function getIcon(): string
    {
        return "warpk.png";
    }

    public function getLoadUnits(): int
    {
        return ReactorWrapperInterface::WARPCORE_LOAD;
    }

    public function getLoadCost(): array
    {
        return ReactorWrapperInterface::WARPCORE_LOAD_COST;
    }

    public function getCapacity(): int
    {
        return $this->getTheoreticalReactorOutput() * ReactorWrapperInterface::WARPCORE_CAPACITY_MULTIPLIER;
    }
}
