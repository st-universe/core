<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Type;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

final class ProjectileWeaponShipSystem extends AbstractSpacecraftSystemType implements SpacecraftSystemTypeInterface
{
    #[\Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::TORPEDO;
    }

    #[\Override]
    public function checkActivationConditions(SpacecraftWrapperInterface $wrapper, string &$reason): bool
    {
        $spacecraft = $wrapper->get();

        if ($spacecraft->getTorpedoCount() === 0) {
            $reason = _('keine Torpedos vorhanden sind');
            return false;
        }

        if ($spacecraft->isCloaked()) {
            $reason = _('die Tarnung aktiviert ist');
            return false;
        }

        if ($wrapper->isUnalerted()) {
            $reason = _('die Alarmstufe Grün ist');
            return false;
        }

        return true;
    }
}
