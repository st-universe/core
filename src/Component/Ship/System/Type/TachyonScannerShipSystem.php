<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Orm\Entity\ShipInterface;

final class TachyonScannerShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    //in seconds
    public const DECLOAK_INTERVAL = 300;

    public const SCAN_EPS_COST = 10;

    public function getEnergyConsumption(): int
    {
        return 3;
    }

    public function activate(ShipInterface $ship, ShipSystemManagerInterface $manager): void
    {
        $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_TACHYON_SCANNER)->setMode(ShipSystemModeEnum::MODE_ON);
    }

    public function deactivate(ShipInterface $ship): void
    {
        $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_TACHYON_SCANNER)->setMode(ShipSystemModeEnum::MODE_OFF);
    }
}
