<?php

namespace Stu\Module\Ship\Lib\Damage;

use Stu\Lib\DamageWrapper;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Entity\ShipSystemInterface;

interface ApplyDamageInterface
{
    public function damage(
        DamageWrapper $damage_wrapper,
        ShipWrapperInterface $shipWrapper,
        InformationInterface $informations
    ): void;

    public function damageBuilding(
        DamageWrapper $damage_wrapper,
        PlanetFieldInterface $target,
        bool $isOrbitField
    ): InformationWrapper;

    public function damageShipSystem(
        ShipWrapperInterface $wrapper,
        ShipSystemInterface $system,
        int $dmg,
        InformationInterface $informations
    ): bool;
}
