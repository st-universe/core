<?php

declare(strict_types=1);

interface ShipAttackCycleInterface
{
    public function cycle();

    public function getMessages();

    public function getProjectileWeaponEnergyCosts(): int;

    public function getEnergyWeaponEnergyCosts(): int;
}
