<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Override;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;

class AggregationSystemShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    public function __construct() {}

    #[Override]
    public function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_AGGREGATION_SYSTEM;
    }

    #[Override]
    public function getEnergyUsageForActivation(): int
    {
        return 15;
    }

    #[Override]
    public function getEnergyConsumption(): int
    {
        return 10;
    }
}
