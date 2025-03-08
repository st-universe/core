<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle\Provider;

use Override;
use RuntimeException;
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Module\Control\StuRandom;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\WeaponInterface;

abstract class AbstractEnergyAttacker implements EnergyAttackerInterface
{
    protected ?ModuleInterface $module = null;
    private ?WeaponInterface $weapon = null;

    public function __construct(
        protected StuRandom $stuRandom
    ) {}

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

        if ($this->getLocation()->getFieldType()->hasEffect(FieldTypeEffectEnum::ENERGY_WEAPON_BUFF)) {
            $damage = (int)ceil($damage / 100 * $this->stuRandom->rand(115, 170, true, 125));
        }
        if ($this->getLocation()->getFieldType()->hasEffect(FieldTypeEffectEnum::ENERGY_WEAPON_NERF)) {
            $damage = (int)ceil($damage / 100 * $this->stuRandom->rand(30, 85, true, 75));
        }

        return $isCritical ? $damage * 2 : $damage;
    }
}
