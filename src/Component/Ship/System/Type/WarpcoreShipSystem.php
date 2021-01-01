<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Orm\Entity\ShipInterface;

final class WarpcoreShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    public function checkActivationConditions(ShipInterface $ship, &$reason): bool
    {
        if ($ship->getWarpcoreLoad() == 0)
        {
            $reason = _('keine Warpkernladung vorhanden ist');
            return false;
        }

        return true;
    }

    public function activate(ShipInterface $ship): void
    {
        $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_WARPCORE)->setMode(ShipSystemModeEnum::MODE_ALWAYS_ON);
    }
    
    public function deactivate(ShipInterface $ship): void
    {
        $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_WARPCORE)->setMode(ShipSystemModeEnum::MODE_OFF);
    }

    public function getEnergyUsageForActivation(): int
    {
        return 0;
    }

    public function getPriority(): int
    {
        return 5;
    }

    public function getEnergyConsumption(): int
    {
        return 0;
    }

    public function getDefaultMode(): int
    {
        return ShipSystemModeEnum::MODE_ALWAYS_ON;
    }
}
