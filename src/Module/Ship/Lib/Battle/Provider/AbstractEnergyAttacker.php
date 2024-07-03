<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\Provider;

use Override;
use RuntimeException;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\WeaponInterface;

abstract class AbstractEnergyAttacker implements EnergyAttackerInterface
{
    protected ?ModuleInterface $module = null;
    private ?WeaponInterface $weapon = null;

    #[Override]
    public function getFiringMode(): int
    {
        $weapon = $this->getWeapon();

        return $weapon->getFiringMode();
    }

    abstract public function getWeaponModule(): ModuleInterface;

    #[Override]
    public function getWeapon(): WeaponInterface
    {
        if ($this->weapon === null) {
            $weapon = $this->getWeaponModule()->getWeapon();
            if ($weapon === null) {
                throw new RuntimeException('module system should have a weapon');
            }

            $this->weapon = $weapon;
        }

        return $this->weapon;
    }

    abstract public function getEnergyWeaponBaseDamage(): int;

    #[Override]
    public function getWeaponDamage(bool $isCritical): int
    {
        $basedamage = $this->getEnergyWeaponBaseDamage();
        $variance = (int) round($basedamage / 100 * $this->getWeapon()->getVariance());
        $damage = random_int($basedamage - $variance, $basedamage + $variance);

        return $isCritical ? $damage * 2 : $damage;
    }
}
