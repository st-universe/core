<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\Provider;

use RuntimeException;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\StuRandom;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\ModuleValueCalculatorInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\TorpedoTypeInterface;
use Stu\Orm\Entity\UserInterface;

class ShipAttacker extends AbstractEnergyAttacker implements ProjectileAttackerInterface
{
    public function __construct(
        private ShipWrapperInterface $wrapper,
        private ModuleValueCalculatorInterface $moduleValueCalculator,
        private ShipTorpedoManagerInterface $shipTorpedoManager,
        private StuRandom $stuRandom,
        private LoggerUtilInterface $logger
    ) {
    }

    public function getPhaserVolleys(): int
    {
        return $this->get()->getRump()->getPhaserVolleys();
    }

    public function getPhaserState(): bool
    {
        return $this->get()->getPhaserState();
    }

    public function hasSufficientEnergy(int $amount): bool
    {
        $epsSystemData = $this->wrapper->getEpsSystemData();
        if ($epsSystemData === null) {
            return false;
        }
        return $epsSystemData->getEps() >= $amount;
    }

    public function getWeaponModule(): ModuleInterface
    {
        if ($this->module === null) {
            $shipSystem = $this->get()->getShipSystem(ShipSystemTypeEnum::SYSTEM_PHASER);

            $module = $shipSystem->getModule();
            if ($module === null) {
                throw new RuntimeException('weapon system should have a module');
            }

            $this->module = $module;
        }

        return $this->module;
    }

    public function getEnergyWeaponBaseDamage(): int
    {
        return $this->moduleValueCalculator->calculateModuleValue(
            $this->get()->getRump(),
            $this->getWeaponModule(),
            'getBaseDamage'
        );
    }

    public function reduceEps(int $amount): void
    {
        $epsSystemData = $this->wrapper->getEpsSystemData();
        if ($epsSystemData === null) {
            return;
        }
        $epsSystemData->lowerEps($amount)->update();
    }

    public function getName(): string
    {
        return $this->get()->getName();
    }

    public function getUser(): UserInterface
    {
        return $this->get()->getUser();
    }

    private function get(): ShipInterface
    {
        return $this->wrapper->get();
    }

    public function getHitChance(): int
    {
        return $this->get()->getHitChance();
    }

    public function getPhaserShieldDamageFactor(): int
    {
        return $this->get()->getRump()->getPhaserShieldDamageFactor();
    }

    public function getPhaserHullDamageFactor(): int
    {
        return $this->get()->getRump()->getPhaserHullDamageFactor();
    }

    public function getTorpedoVolleys(): int
    {
        return $this->get()->getRump()->getTorpedoVolleys();
    }

    public function getTorpedoState(): bool
    {
        return $this->get()->getTorpedoState();
    }

    public function getTorpedoCount(): int
    {
        return $this->get()->getTorpedoCount();
    }

    public function getTorpedo(): ?TorpedoTypeInterface
    {
        return $this->get()->getTorpedo();
    }

    public function lowerTorpedoCount(int $amount): void
    {
        $this->shipTorpedoManager->changeTorpedo($this->wrapper, -$amount);
    }

    public function isShieldPenetration(): bool
    {
        $systemData = $this->wrapper->getProjectileLauncherSystemData();
        if ($systemData === null) {
            throw new RuntimeException('this should not happen');
        }

        return $this->stuRandom->rand(1, 10000) <= $systemData->getShieldPenetration();
    }

    public function getProjectileWeaponDamage(bool $isCritical): int
    {
        $torpedo = $this->getTorpedo();
        if ($torpedo === null) {
            $this->logger->log('shipAttacker->getProjectileWeaponDamage: no torpedo');
            return 0;
        }

        $module = $this->get()->getShipSystem(ShipSystemTypeEnum::SYSTEM_TORPEDO)->getModule();
        if ($module === null) {
            $this->logger->log('shipAttacker->getProjectileWeaponDamage: no module');
            return 0;
        }

        $variance = (int) round($torpedo->getBaseDamage() / 100 * $torpedo->getVariance());
        $basedamage = $this->moduleValueCalculator->calculateModuleValue(
            $this->get()->getRump(),
            $module,
            false,
            $torpedo->getBaseDamage()
        );
        $damage = random_int($basedamage - $variance, $basedamage + $variance);

        if ($damage === 0) {
            $this->logger->logf(
                'shipAttacker->getProjectileWeaponDamage, baseDamage: %d',
                $torpedo->getBaseDamage()
            );
        }

        return $isCritical ? $damage * 2 : $damage;
    }
}
