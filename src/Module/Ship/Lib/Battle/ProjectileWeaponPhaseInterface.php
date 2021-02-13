<?php

namespace Stu\Module\Ship\Lib\Battle;

interface ProjectileWeaponPhaseInterface
{
    public function fire($attacker, array $targetPool, bool $isAlertRed = false): array;
}
