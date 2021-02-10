<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\AttackBuilding;

use Stu\Orm\Entity\ColonyInterface;

final class EnergyPhalanx
{

    private ColonyInterface $colony;

    public function __construct(
        ColonyInterface $colony
    ) {
        $this->colony = $colony;
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

    // ShipRumpInterface stuff

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
