<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\Provider;

use Override;
use RuntimeException;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;

final class EnergyPhalanx extends AbstractEnergyAttacker
{
    public function __construct(private ColonyInterface $colony, private ModuleRepositoryInterface $moduleRepository)
    {
    }

    #[Override]
    public function hasSufficientEnergy(int $amount): bool
    {
        return $this->getEps() >= $amount;
    }

    #[Override]
    public function getFiringMode(): int
    {
        $weapon = $this->getWeapon();

        return $weapon->getFiringMode();
    }

    private function getEps(): int
    {
        return $this->colony->getEps();
    }

    #[Override]
    public function reduceEps(int $amount): void
    {
        $this->colony->setEps($this->getEps() - $amount);
    }

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->colony->getUser();
    }

    private function isDisruptor(): bool
    {
        return in_array($this->colony->getUser()->getFactionId(), [2, 3]);
    }

    #[Override]
    public function getName(): string
    {
        return $this->isDisruptor() ? 'Orbitale Disruptorphalanx' : 'Orbitale Phaserphalanx';
    }

    #[Override]
    public function getPhaserState(): bool
    {
        return true;
    }

    #[Override]
    public function getHitChance(): int
    {
        return $this->isDisruptor() ? 67 : 86;
    }

    private function getModuleId(): int
    {
        return $this->isDisruptor() ? 3 : 1;
    }

    #[Override]
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

    #[Override]
    public function getEnergyWeaponBaseDamage(): int
    {
        return $this->isDisruptor() ? 180 : 250;
    }

    #[Override]
    public function getPhaserVolleys(): int
    {
        return $this->isDisruptor() ? 5 : 3;
    }

    #[Override]
    public function getPhaserShieldDamageFactor(): int
    {
        return 200;
    }

    #[Override]
    public function getPhaserHullDamageFactor(): int
    {
        return 100;
    }
}
