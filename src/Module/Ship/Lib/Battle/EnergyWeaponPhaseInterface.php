<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

interface EnergyWeaponPhaseInterface
{
    public function fire($attacker, array $targetPool, bool $isAlertRed = false): array;

    public function getEnergyWeaponEnergyCosts(): int;
}
