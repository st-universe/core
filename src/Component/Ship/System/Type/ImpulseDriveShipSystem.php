<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;

final class ImpulseDriveShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    public function getSystemType(): int
    {
        return ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE;
    }

    public function getEnergyConsumption(): int
    {
        return 0;
    }
}
