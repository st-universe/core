<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Type;

use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

final class EnergyWeaponShipSystem extends AbstractSpacecraftSystemType implements SpacecraftSystemTypeInterface
{
    #[Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::PHASER;
    }

    #[Override]
    public function checkActivationConditions(SpacecraftWrapperInterface $wrapper, string &$reason): bool
    {
        $ship = $wrapper->get();

        if ($ship->isCloaked()) {
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
