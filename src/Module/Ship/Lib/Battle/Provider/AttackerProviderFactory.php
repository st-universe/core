<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\Provider;

use Override;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;

class AttackerProviderFactory implements AttackerProviderFactoryInterface
{
    public function __construct(
        private ShipTorpedoManagerInterface $shipTorpedoManager,
        private ModuleRepositoryInterface $moduleRepository,
        private ColonyStorageManagerInterface $colonyStorageManager,
        private StuRandom $stuRandom
    ) {
    }

    #[Override]
    public function getShipAttacker(ShipWrapperInterface $wrapper): ShipAttacker
    {
        return new ShipAttacker(
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
            $this->colonyStorageManager
        );
    }
}
