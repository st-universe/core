<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Data;

use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\ReactorWrapperInterface;

class FusionCoreSystemData extends AbstractReactorSystemData
{
    function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_FUSION_REACTOR;
    }

    public function getIcon(): string
    {
        return "fusrkt.png";
    }

    public function getLoadUnits(): int
    {
        return ReactorWrapperInterface::FUSION_REACTOR_LOAD;
    }

    public function getLoadCost(): array
    {
        return ReactorWrapperInterface::FUSION_REACTOR_LOAD_COST;
    }

    public function getCapacity(): int
    {
        return $this->getTheoreticalReactorOutput() * ReactorWrapperInterface::FUSIONCORE_CAPACITY_MULTIPLIER;
    }
}
