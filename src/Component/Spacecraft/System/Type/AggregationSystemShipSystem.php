<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Type;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeInterface;

class AggregationSystemShipSystem extends AbstractSpacecraftSystemType implements SpacecraftSystemTypeInterface
{
    #[\Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::AGGREGATION_SYSTEM;
    }

    #[\Override]
    public function getEnergyUsageForActivation(): int
    {
        return 15;
    }

    #[\Override]
    public function getEnergyConsumption(): int
    {
        return 10;
    }
}
