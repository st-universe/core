<?php

namespace Stu\Module\Ship\Lib;

interface ShipAttackCycleInterface
{
    public function cycle();

    public function getMessages();

    public function getProjectileWeaponEnergyCosts(): int;

    public function getEnergyWeaponEnergyCosts(): int;
}
