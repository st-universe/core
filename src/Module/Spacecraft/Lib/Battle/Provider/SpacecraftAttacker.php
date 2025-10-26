<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle\Provider;

use RuntimeException;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\StuRandom;
use Stu\Module\Spacecraft\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\TorpedoType;

class SpacecraftAttacker extends AbstractEnergyAttacker implements ProjectileAttackerInterface
{
    public function __construct(
        private SpacecraftWrapperInterface $wrapper,
        private ShipTorpedoManagerInterface $shipTorpedoManager,
        private bool $isAttackingShieldsOnly,
        StuRandom $stuRandom
    ) {
        parent::__construct($stuRandom);
    }

    #[\Override]
    public function isAvoidingHullHits(Spacecraft $target): bool
    {
        return $this->isAttackingShieldsOnly && !$target->isShielded();
    }

    #[\Override]
    public function getPhaserVolleys(): int
    {
        return $this->get()->getRump()->getPhaserVolleys();
    }

    #[\Override]
    public function getPhaserState(): bool
    {
        return $this->get()->getPhaserState();
    }

    #[\Override]
    public function hasSufficientEnergy(int $amount): bool
    {
        $epsSystemData = $this->wrapper->getEpsSystemData();
        if ($epsSystemData === null) {
            return false;
        }
        return $epsSystemData->getEps() >= $amount;
    }

    #[\Override]
    public function getWeaponModule(): Module
    {
        if ($this->module === null) {
            $shipSystem = $this->get()->getSpacecraftSystem(SpacecraftSystemTypeEnum::PHASER);

            $module = $shipSystem->getModule();
            if ($module === null) {
                throw new RuntimeException('weapon system should have a module');
            }

            $this->module = $module;
        }

        return $this->module;
    }

    #[\Override]
    public function getEnergyWeaponBaseDamage(): int
    {
        $energyWeapon = $this->wrapper->getEnergyWeaponSystemData();

        return $energyWeapon === null ? 0 : $energyWeapon->getBaseDamage();
    }

    #[\Override]
    public function reduceEps(int $amount): void
    {
        $epsSystemData = $this->wrapper->getEpsSystemData();
        if ($epsSystemData === null) {
            return;
        }
        $epsSystemData->lowerEps($amount)->update();
    }

    #[\Override]
    public function getName(): string
    {
        return $this->get()->getName();
    }

    #[\Override]
    public function getUserId(): int
    {
        return $this->get()->getUser()->getId();
    }

    private function get(): Spacecraft
    {
        return $this->wrapper->get();
    }

    #[\Override]
    public function getHitChance(): int
    {
        return $this->wrapper->getComputerSystemDataMandatory()->getHitChance();
    }

    #[\Override]
    public function getPhaserShieldDamageFactor(): int
    {
        return $this->get()->getRump()->getPhaserShieldDamageFactor();
    }

    #[\Override]
    public function getPhaserHullDamageFactor(): int
    {
        return $this->get()->getRump()->getPhaserHullDamageFactor();
    }

    #[\Override]
    public function getTorpedoVolleys(): int
    {
        return $this->get()->getRump()->getTorpedoVolleys();
    }

    #[\Override]
    public function getTorpedoState(): bool
    {
        return $this->get()->getTorpedoState();
    }

    #[\Override]
    public function getTorpedoCount(): int
    {
        return $this->get()->getTorpedoCount();
    }

    #[\Override]
    public function getTorpedo(): ?TorpedoType
    {
        return $this->get()->getTorpedo();
    }

    #[\Override]
    public function lowerTorpedoCount(int $amount): void
    {
        $this->shipTorpedoManager->changeTorpedo($this->wrapper, -$amount);
    }

    #[\Override]
    public function isShieldPenetration(): bool
    {
        $systemData = $this->wrapper->getProjectileLauncherSystemData();
        if ($systemData === null) {
            throw new RuntimeException('this should not happen');
        }

        return $this->stuRandom->rand(1, 10000) <= $systemData->getShieldPenetration();
    }

    #[\Override]
    public function getProjectileWeaponDamage(bool $isCritical): int
    {
        $torpedo = $this->getTorpedo();
        if ($torpedo === null) {
            return 0;
        }

        $module = $this->get()->getSpacecraftSystem(SpacecraftSystemTypeEnum::TORPEDO)->getModule();
        if ($module === null) {
            return 0;
        }

        $variance = (int) round($torpedo->getBaseDamage() / 100 * $torpedo->getVariance());
        $basedamage = $torpedo->getBaseDamage();
        $damage = random_int($basedamage - $variance, $basedamage + $variance);

        return $isCritical ? $damage * 2 : $damage;
    }

    #[\Override]
    public function getLocation(): Location
    {
        return $this->get()->getLocation();
    }
}
