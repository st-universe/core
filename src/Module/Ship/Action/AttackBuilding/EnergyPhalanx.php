<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\AttackBuilding;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;

final class EnergyPhalanx
{
    private ColonyInterface $colony;

    private ModuleRepositoryInterface $moduleRepository;

    private bool $isDisruptor;

    public function __construct(
        ColonyInterface $colony,
        ModuleRepositoryInterface $moduleRepository
    ) {
        $this->colony = $colony;
        $this->moduleRepository = $moduleRepository;
        $this->isDisruptor = in_array($colony->getUser()->getFactionId(), [2, 3]);
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
        return $this->isDisruptor ? 'Orbitale Disruptorphalanx' : 'Orbitale Phaserphalanx';
    }

    public function getPhaserState(): bool
    {
        return true;
    }

    public function getHitChance(): int
    {
        return $this->isDisruptor ? 67 : 86;
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
        return $this->isDisruptor ? 3 : 1;
    }

    public function getModule()
    {
        return $this->moduleRepository->find($this->getModuleId());
    }

    // ShipRumpInterface stuff

    public function getRoleId()
    {
        return 0;
    }

    public function getBaseDamage()
    {
        return $this->isDisruptor ? 180 : 250;
    }

    public function getModuleLevel()
    {
        return 1;
    }

    public function getPhaserVolleys(): int
    {
        return $this->isDisruptor ? 5 : 3;
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
