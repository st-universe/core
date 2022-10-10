<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\AttackBuilding;

use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ModuleInterface;

final class ProjectilePhalanx
{

    private ColonyInterface $colony;

    private ModuleInterface $module;

    private ColonyStorageManagerInterface $colonyStorageManager;

    public function __construct(
        ColonyInterface $colony,
        ModuleInterface $module,
        ColonyStorageManagerInterface $colonyStorageManager
    ) {
        $this->colony = $colony;
        $this->module = $module;
        $this->colonyStorageManager = $colonyStorageManager;
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
        return 'Orbitale Torpedophalanx';
    }

    public function getTorpedos(): bool
    {
        return $this->getTorpedoCount() > 0;
    }

    public function getHitChance(): int
    {
        return 75;
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

    public function setMode(int $foo)
    {
    }

    public function getModuleId()
    {
        return 2;
    }

    public function getModule()
    {
        return $this->module;
    }

    public function setTorpedoCount(int $torpedoAmount)
    {
        $this->colonyStorageManager->lowerStorage($this->colony, $this->getTorpedo()->getCommodity(), 1);
    }

    public function getTorpedoCount(): int
    {
        if ($this->getTorpedo() != null) {
            $stor = $this->colony->getStorage()[$this->getTorpedo()->getCommodityId()];

            if ($stor) {
                return $stor->getAmount();
            }
        }

        return  0;
    }

    public function getTorpedo()
    {
        return $this->colony->getTorpedo();
    }

    // ShipRumpInterface stuff

    public function getTorpedoVolleys()
    {
        return 7;
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
        return 200;
    }

    public function getPhaserHullDamageFactor(): int
    {
        return 100;
    }

    public function getRoleId()
    {
        return 0;
    }
}
