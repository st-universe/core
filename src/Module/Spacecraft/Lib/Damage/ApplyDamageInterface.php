<?php

namespace Stu\Module\Spacecraft\Lib\Damage;

use Stu\Lib\Damage\DamageWrapper;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Entity\SpacecraftSystemInterface;

interface ApplyDamageInterface
{
    public function damage(
        DamageWrapper $damage_wrapper,
        SpacecraftWrapperInterface $shipWrapper,
        InformationInterface $informations
    ): void;

    public function damageBuilding(
        DamageWrapper $damage_wrapper,
        PlanetFieldInterface $target,
        bool $isOrbitField
    ): InformationWrapper;

    public function damageShipSystem(
        SpacecraftWrapperInterface $wrapper,
        SpacecraftSystemInterface $system,
        int $dmg,
        InformationInterface $informations
    ): bool;
}
