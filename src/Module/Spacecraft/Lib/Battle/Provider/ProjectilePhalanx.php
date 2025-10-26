<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle\Provider;

use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\TorpedoType;

final class ProjectilePhalanx implements ProjectileAttackerInterface
{
    public function __construct(
        private Colony $colony,
        private StorageManagerInterface $storageManager
    ) {}

    #[\Override]
    public function hasSufficientEnergy(int $amount): bool
    {
        return $this->getEps() >= $amount;
    }

    #[\Override]
    public function isAvoidingHullHits(Spacecraft $target): bool
    {
        return false;
    }

    private function getEps(): int
    {
        return $this->colony->getChangeable()->getEps();
    }

    #[\Override]
    public function reduceEps(int $amount): void
    {
        $this->colony->getChangeable()->lowerEps($amount);
    }

    #[\Override]
    public function getName(): string
    {
        return 'Orbitale Torpedophalanx';
    }

    #[\Override]
    public function getTorpedoState(): bool
    {
        return $this->getTorpedoCount() > 0;
    }

    #[\Override]
    public function getHitChance(): int
    {
        return 75;
    }

    #[\Override]
    public function getUserId(): int
    {
        return $this->colony->getUser()->getId();
    }

    #[\Override]
    public function getTorpedoCount(): int
    {
        $torpedo = $this->getTorpedo();

        if ($torpedo != null) {
            $stor = $this->colony->getStorage()->get($torpedo->getCommodityId());

            if ($stor !== null) {
                return $stor->getAmount();
            }
        }

        return  0;
    }

    #[\Override]
    public function lowerTorpedoCount(int $amount): void
    {
        $torpedo = $this->getTorpedo();
        if ($torpedo === null) {
            return;
        }

        $this->storageManager->lowerStorage(
            $this->colony,
            $torpedo->getCommodity(),
            $amount
        );
    }

    #[\Override]
    public function isShieldPenetration(): bool
    {
        return false;
    }

    #[\Override]
    public function getTorpedo(): ?TorpedoType
    {
        return $this->colony->getChangeable()->getTorpedo();
    }

    #[\Override]
    public function getTorpedoVolleys(): int
    {
        return 7;
    }

    #[\Override]
    public function getProjectileWeaponDamage(bool $isCritical): int
    {
        $torpedo = $this->getTorpedo();
        if ($torpedo === null) {
            return 0;
        }

        $basedamage = $torpedo->getBaseDamage();
        $variance = (int) round($basedamage / 100 * $torpedo->getVariance());
        $damage = random_int($basedamage - $variance, $basedamage + $variance);

        return $isCritical ? $damage * 2 : $damage;
    }

    #[\Override]
    public function getLocation(): Location
    {
        return $this->colony->getLocation();
    }
}
