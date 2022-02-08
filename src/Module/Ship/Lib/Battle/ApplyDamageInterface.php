<?php

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Lib\DamageWrapper;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\ShipInterface;

interface ApplyDamageInterface
{
    public function damage(DamageWrapper $damage_wrapper, ShipInterface $ship): array;

    public function damageBuilding(
        DamageWrapper $damage_wrapper,
        PlanetField $target,
        $isOrbitField
    ): array;

    public function damageShipSystem($ship, $system, $dmg, &$msg): bool;
}
