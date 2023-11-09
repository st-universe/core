<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Data;

use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\ReactorWrapperInterface;

class SingularityCoreSystemData extends AbstractReactorSystemData
{
    function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_SINGULARITY_REACTOR;
    }

    public function getIcon(): string
    {
        return "singrkt.png";
    }

    public function getLoadUnits(): int
    {
        return ReactorWrapperInterface::SINGULARITY_CORE_LOAD;
    }

    public function getLoadCost(): array
    {
        return ReactorWrapperInterface::SINGULARITY_CORE_LOAD_COST;
    }

    public function getCapacity(): int
    {
        return $this->getTheoreticalReactorOutput() * ReactorWrapperInterface::SINGULARITY_CAPACITY_MULTIPLIER;
    }
}
