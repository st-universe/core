<?php

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Orm\Entity\ShipInterface;

interface ProjectileWeaponPhaseInterface
{
    public function fire(ShipInterface $attacker, array $targetPool): array;
}
