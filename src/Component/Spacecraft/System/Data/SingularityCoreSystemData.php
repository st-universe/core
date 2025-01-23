<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Data;

use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Spacecraft\Lib\ReactorWrapperInterface;

class SingularityCoreSystemData extends AbstractReactorSystemData
{
    #[Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::SINGULARITY_REACTOR;
    }

    #[Override]
    public function getIcon(): string
    {
        return "singrkt.png";
    }

    #[Override]
    public function getLoadUnits(): int
    {
        return ReactorWrapperInterface::SINGULARITY_CORE_LOAD;
    }

    #[Override]
    public function getLoadCost(): array
    {
        return ReactorWrapperInterface::SINGULARITY_CORE_LOAD_COST;
    }

    #[Override]
    public function getCapacity(): int
    {
        return $this->getTheoreticalReactorOutput() * ReactorWrapperInterface::SINGULARITY_CAPACITY_MULTIPLIER;
    }
}
