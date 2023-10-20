<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;

final class TachyonScannerShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    //in seconds
    public const DECLOAK_INTERVAL = 300;

    public const SCAN_EPS_COST = 10;

    public function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_TACHYON_SCANNER;
    }

    public function getEnergyConsumption(): int
    {
        return 3;
    }
}
