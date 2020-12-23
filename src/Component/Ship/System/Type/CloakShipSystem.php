<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Orm\Entity\ShipInterface;

final class CloakShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    public function getEnergyUsageForActivation(): int
    {
        return 10;
    }

    public function getEnergyConsumption(): int
    {
        return 8;
    }

    public function activate(ShipInterface $ship): void
    {
        $ship->deactivateTraktorBeam();
        $ship->setDockedTo(null);
        $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_SHIELDS)->setMode(ShipSystemModeEnum::MODE_OFF);
        $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_CLOAK)->setMode(ShipSystemModeEnum::MODE_ON);
    }
    
    public function deactivate(ShipInterface $ship): void
    {
        $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_CLOAK)->setMode(ShipSystemModeEnum::MODE_OFF);
    }
}
