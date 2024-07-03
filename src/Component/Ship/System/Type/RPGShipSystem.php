<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Override;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;

final class RPGShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    #[Override]
    public function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_RPG_MODULE;
    }

    #[Override]
    public function getEnergyUsageForActivation(): int
    {
        return 0;
    }

    #[Override]
    public function getEnergyConsumption(): int
    {
        return 0;
    }
}
