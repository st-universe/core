<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Data;

use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Spacecraft\Lib\ReactorWrapperInterface;

class FusionCoreSystemData extends AbstractReactorSystemData
{
    #[Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::SYSTEM_FUSION_REACTOR;
    }

    #[Override]
    public function getIcon(): string
    {
        return "fusrkt.png";
    }

    #[Override]
    public function getLoadUnits(): int
    {
        return ReactorWrapperInterface::FUSION_REACTOR_LOAD;
    }

    #[Override]
    public function getLoadCost(): array
    {
        return ReactorWrapperInterface::FUSION_REACTOR_LOAD_COST;
    }

    #[Override]
    public function getCapacity(): int
    {
        return $this->getTheoreticalReactorOutput() * ReactorWrapperInterface::FUSIONCORE_CAPACITY_MULTIPLIER;
    }
}
