<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

final class EnergyWeaponShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    public function checkActivationConditions(ShipInterface $ship, &$reason): bool
    {
        if ($ship->getCloakState()) {
            $reason = _('die Tarnung aktiviert ist');
            return false;
        }

        if ($ship->isAlertGreen()) {
            $reason = _('die Alarmstufe Grün ist');
            return false;
        }

        return true;
    }

    public function getEnergyUsageForActivation(): int
    {
        return 1;
    }

    public function activate(ShipWrapperInterface $wrapper, ShipSystemManagerInterface $manager): void
    {
        $wrapper->get()->getShipSystem(ShipSystemTypeEnum::SYSTEM_PHASER)->setMode(ShipSystemModeEnum::MODE_ON);
    }

    public function deactivate(ShipWrapperInterface $wrapper): void
    {
        $wrapper->get()->getShipSystem(ShipSystemTypeEnum::SYSTEM_PHASER)->setMode(ShipSystemModeEnum::MODE_OFF);
    }
}
