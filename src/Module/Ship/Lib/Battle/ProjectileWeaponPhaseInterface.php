<?php

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Entity\ShipInterface;

interface ProjectileWeaponPhaseInterface
{
    public function fire($attacker, array $targetPool, bool $isAlertRed = false): array;

    public function fireAtBuilding(
        ShipInterface $attacker,
        PlanetFieldInterface $target,
        $isOrbitField,
        &$antiParticleCount
    ): array;
}
