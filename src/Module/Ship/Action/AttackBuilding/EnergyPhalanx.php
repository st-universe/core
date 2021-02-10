<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\AttackBuilding;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ModuleInterface;

final class EnergyPhalanx
{

    private ColonyInterface $colony;

    private ModuleInterface $module;

    public function __construct(
        ColonyInterface $colony,
        ModuleInterface $module
    ) {
        $this->colony = $colony;
        $this->module = $module;
    }

    // ShipInterface stuff
    public function getRump()
    {
        return $this;
    }

    public function getEps(): int
    {
        return $this->colony->getEps();
    }

    public function setEps(int $eps)
    {
        $this->colony->setEps($eps);
    }

    public function getName(): string
    {
        return 'Orbitale Phaserphalanx';
    }

    public function getPhaser(): bool
    {
        return true;
    }

    public function getHitChance(): int
    {
        return 100;
    }

    public function getUser()
    {
        return $this->colony->getUser();
    }

    public function hasShipSystem(int $foo)
    {
        return true;
    }

    public function getShipSystem(int $foo)
    {
        return $this;
    }

    public function getModuleId()
    {
        return 1;
    }

    public function getModule()
    {
        return $this->module;
    }

    // ShipRumpInterface stuff

    public function getBaseDamage()
    {
        return 500;
    }

    public function getModuleLevel()
    {
        return 1;
    }

    public function getPhaserVolleys(): int
    {
        return 1;
    }

    public function getPhaserShieldDamageFactor(): int
    {
        return 2;
    }

    public function getPhaserHullDamageFactor(): int
    {
        return 1;
    }
}
