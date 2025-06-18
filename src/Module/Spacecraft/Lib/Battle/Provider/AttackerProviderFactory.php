<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle\Provider;

use Override;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Spacecraft\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;

class AttackerProviderFactory implements AttackerProviderFactoryInterface
{
    public function __construct(
        private ShipTorpedoManagerInterface $shipTorpedoManager,
        private ModuleRepositoryInterface $moduleRepository,
        private StorageManagerInterface $storageManager,
        private StuRandom $stuRandom
    ) {}

    #[Override]
    public function getSpacecraftAttacker(SpacecraftWrapperInterface $wrapper): SpacecraftAttacker
    {
        return new SpacecraftAttacker(
            $wrapper,
            $this->shipTorpedoManager,
            $this->stuRandom
        );
    }

    #[Override]
    public function getEnergyPhalanxAttacker(ColonyInterface $colony): EnergyAttackerInterface
    {
        return new EnergyPhalanx(
            $colony,
            $this->moduleRepository
        );
    }

    #[Override]
    public function getProjectilePhalanxAttacker(ColonyInterface $colony): ProjectileAttackerInterface
    {
        return new ProjectilePhalanx(
            $colony,
            $this->storageManager
        );
    }
}
