<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Orm\Entity\ShipInterface;

final class TroopQuartersShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    public function activate(ShipInterface $ship): void
    {
        $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS)->setMode(ShipSystemModeEnum::MODE_ON);
    }
    
    public function deactivate(ShipInterface $ship): void
    {
        //TODO check if troop quarters empty?
        $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS)->setMode(ShipSystemModeEnum::MODE_OFF);
    }

    public function handleDestruction(ShipInterface $ship): void
    {
        //TODO let troops die
    }
    
    public function handleDamage(ShipInterface $ship): void
    {
        //TODO let troops die
    }

    public function getEnergyUsageForActivation(): int
    {
        return 5;
    }

    public function getPriority(): int
    {
        return 3;
    }

    public function getEnergyConsumption(): int
    {
        return 5;
    }
}
