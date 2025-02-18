<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle\Provider;

use Stu\Orm\Entity\WeaponInterface;

interface EnergyAttackerInterface extends AttackerInterface
{
    public function getPhaserVolleys(): int;

    public function getPhaserState(): bool;

    public function getFiringMode(): int;

    public function getWeapon(): WeaponInterface;

    public function getWeaponDamage(bool $isCritical): int;

    public function getPhaserShieldDamageFactor(): int;

    public function getPhaserHullDamageFactor(): int;
}
