<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle\Provider;

use Stu\Orm\Entity\TorpedoTypeInterface;

interface ProjectileAttackerInterface extends AttackerInterface
{
    public function getTorpedoVolleys(): int;

    public function getTorpedoState(): bool;

    public function getTorpedoCount(): int;

    public function getTorpedo(): ?TorpedoTypeInterface;

    public function lowerTorpedoCount(int $amount): void;

    public function getProjectileWeaponDamage(bool $isCritical): int;

    public function isShieldPenetration(): bool;
}
