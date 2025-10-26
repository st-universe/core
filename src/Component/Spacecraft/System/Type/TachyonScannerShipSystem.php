<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Type;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeInterface;

final class TachyonScannerShipSystem extends AbstractSpacecraftSystemType implements SpacecraftSystemTypeInterface
{
    //in seconds
    public const int DECLOAK_INTERVAL = 300;

    public const int SCAN_EPS_COST = 10;

    #[\Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::TACHYON_SCANNER;
    }

    #[\Override]
    public function getEnergyConsumption(): int
    {
        return 3;
    }
}
