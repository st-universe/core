<?php

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Lib\DamageWrapper;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\PlanetFieldInterface;

interface ApplyDamageInterface
{
    public function damage(
        DamageWrapper $damage_wrapper,
        ShipWrapperInterface $shipWrapper
    ): array;

    public function damageBuilding(
        DamageWrapper $damage_wrapper,
        PlanetFieldInterface $target,
        bool $isOrbitField
    ): array;

    public function damageShipSystem(
        ShipWrapperInterface $wrapper,
        $system,
        $dmg,
        &$msg
    ): bool;
}
