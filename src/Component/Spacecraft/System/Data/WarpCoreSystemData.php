<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Data;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Spacecraft\Lib\ReactorWrapperInterface;

class WarpCoreSystemData extends AbstractReactorSystemData
{
    #[\Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::WARPCORE;
    }

    #[\Override]
    public function getIcon(): string
    {
        return "warpk.png";
    }

    #[\Override]
    public function getLoadUnits(): int
    {
        return ReactorWrapperInterface::WARPCORE_LOAD;
    }

    #[\Override]
    public function getLoadCost(): array
    {
        return ReactorWrapperInterface::WARPCORE_LOAD_COST;
    }

    #[\Override]
    public function getCapacity(): int
    {
        return $this->getTheoreticalReactorOutput() * ReactorWrapperInterface::WARPCORE_CAPACITY_MULTIPLIER;
    }
}
