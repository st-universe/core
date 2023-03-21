<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\Provider;

use Stu\Orm\Entity\TorpedoTypeInterface;
use Stu\Orm\Entity\UserInterface;

interface ProjectileAttackerInterface
{
    public function getTorpedoVolleys(): int;

    public function getTorpedoState(): bool;

    public function hasSufficientEnergy(int $amount): bool;

    public function reduceEps(int $amount): void;

    public function getTorpedoCount(): int;

    public function getTorpedo(): ?TorpedoTypeInterface;

    public function lowerTorpedoCount(int $amount): void;

    public function getUser(): UserInterface;

    public function getName(): string;

    public function getHitChance(): int;

    public function getProjectileWeaponDamage(bool $isCritical): int;
}
