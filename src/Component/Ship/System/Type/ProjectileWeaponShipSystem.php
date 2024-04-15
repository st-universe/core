<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

final class ProjectileWeaponShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    public function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_TORPEDO;
    }

    public function checkActivationConditions(ShipWrapperInterface $wrapper, string &$reason): bool
    {
        $ship = $wrapper->get();

        if ($ship->getTorpedoCount() === 0) {
            $reason = _('keine Torpedos vorhanden sind');
            return false;
        }

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
}
