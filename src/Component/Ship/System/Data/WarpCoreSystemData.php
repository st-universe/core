<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Data;

use Override;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\ReactorWrapperInterface;

class WarpCoreSystemData extends AbstractReactorSystemData
{
    #[Override]
    function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_WARPCORE;
    }

    #[Override]
    public function getIcon(): string
    {
        return "warpk.png";
    }

    #[Override]
    public function getLoadUnits(): int
    {
        return ReactorWrapperInterface::WARPCORE_LOAD;
    }

    #[Override]
    public function getLoadCost(): array
    {
        return ReactorWrapperInterface::WARPCORE_LOAD_COST;
    }

    #[Override]
    public function getCapacity(): int
    {
        return $this->getTheoreticalReactorOutput() * ReactorWrapperInterface::WARPCORE_CAPACITY_MULTIPLIER;
    }
}
