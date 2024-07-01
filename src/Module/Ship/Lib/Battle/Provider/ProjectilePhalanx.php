<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\Provider;

use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\TorpedoTypeInterface;
use Stu\Orm\Entity\UserInterface;

final class ProjectilePhalanx implements ProjectileAttackerInterface
{
    public function __construct(
        private ColonyInterface $colony,
        private ColonyStorageManagerInterface $colonyStorageManager
    ) {
    }

    public function hasSufficientEnergy(int $amount): bool
    {
        return $this->getEps() >= $amount;
    }

    private function getEps(): int
    {
        return $this->colony->getEps();
    }

    public function reduceEps(int $amount): void
    {
        $this->colony->setEps($this->getEps() - $amount);
    }

    public function getName(): string
    {
        return 'Orbitale Torpedophalanx';
    }

    public function getTorpedoState(): bool
    {
        return $this->getTorpedoCount() > 0;
    }

    public function getHitChance(): int
    {
        return 75;
    }

    public function getUser(): UserInterface
    {
        return $this->colony->getUser();
    }

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

    public function lowerTorpedoCount(int $amount): void
    {
        $torpedo = $this->getTorpedo();
        if ($torpedo === null) {
            return;
        }

        $this->colonyStorageManager->lowerStorage(
            $this->colony,
            $torpedo->getCommodity(),
            $amount
        );
    }

    public function isShieldPenetration(): bool
    {
        return false;
    }

    public function getTorpedo(): ?TorpedoTypeInterface
    {
        return $this->colony->getTorpedo();
    }

    public function getTorpedoVolleys(): int
    {
        return 7;
    }

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
}
