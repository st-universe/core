<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\Provider;

use RuntimeException;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;

final class EnergyPhalanx extends AbstractEnergyAttacker
{
    private ColonyInterface $colony;

    private ModuleRepositoryInterface $moduleRepository;

    public function __construct(
        ColonyInterface $colony,
        ModuleRepositoryInterface $moduleRepository
    ) {
        $this->colony = $colony;
        $this->moduleRepository = $moduleRepository;
    }

    public function hasSufficientEnergy(int $amount): bool
    {
        return $this->getEps() >= $amount;
    }

    public function getFiringMode(): int
    {
        $weapon = $this->getWeapon();

        return $weapon->getFiringMode();
    }

    private function getEps(): int
    {
        return $this->colony->getEps();
    }

    public function reduceEps(int $amount): void
    {
        $this->colony->setEps($this->getEps() - $amount);
    }

    public function getUser(): UserInterface
    {
        return $this->colony->getUser();
    }

    private function isDisruptor(): bool
    {
        return in_array($this->colony->getUser()->getFactionId(), [2, 3]);
    }

    public function getName(): string
    {
        return $this->isDisruptor() ? 'Orbitale Disruptorphalanx' : 'Orbitale Phaserphalanx';
    }

    public function getPhaserState(): bool
    {
        return true;
    }

    public function getHitChance(): int
    {
        return $this->isDisruptor() ? 67 : 86;
    }

    private function getModuleId(): int
    {
        return $this->isDisruptor() ? 3 : 1;
    }

    public function getWeaponModule(): ModuleInterface
    {
        if ($this->module === null) {
            $module = $this->moduleRepository->find($this->getModuleId());
            if ($module === null) {
                throw new RuntimeException('module not existent');
            }

            $this->module = $module;
        }

        return $this->module;
    }

    public function getEnergyWeaponBaseDamage(): int
    {
        return $this->isDisruptor() ? 180 : 250;
    }

    public function getPhaserVolleys(): int
    {
        return $this->isDisruptor() ? 5 : 3;
    }

    public function getPhaserShieldDamageFactor(): int
    {
        return 200;
    }

    public function getPhaserHullDamageFactor(): int
    {
        return 100;
    }
}
