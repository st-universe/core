<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\Provider;

use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Module\Ship\Lib\ModuleValueCalculatorInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;

class AttackerProviderFactory implements AttackerProviderFactoryInterface
{
    private ModuleValueCalculatorInterface $moduleValueCalculator;

    private ShipTorpedoManagerInterface $shipTorpedoManager;

    private ModuleRepositoryInterface $moduleRepository;

    private ColonyStorageManagerInterface $colonyStorageManager;

    public function __construct(
        ModuleValueCalculatorInterface $moduleValueCalculator,
        ShipTorpedoManagerInterface $shipTorpedoManager,
        ModuleRepositoryInterface $moduleRepository,
        ColonyStorageManagerInterface $colonyStorageManager
    ) {
        $this->moduleValueCalculator = $moduleValueCalculator;
        $this->shipTorpedoManager = $shipTorpedoManager;
        $this->moduleRepository = $moduleRepository;
        $this->colonyStorageManager = $colonyStorageManager;
    }

    public function getShipAttacker(ShipWrapperInterface $wrapper): ShipAttacker
    {
        return new ShipAttacker(
            $wrapper,
            $this->moduleValueCalculator,
            $this->shipTorpedoManager
        );
    }

    public function getEnergyPhalanxAttacker(ColonyInterface $colony): EnergyAttackerInterface
    {
        return new EnergyPhalanx(
            $colony,
            $this->moduleRepository
        );
    }

    public function getProjectilePhalanxAttacker(ColonyInterface $colony): ProjectileAttackerInterface
    {
        return new ProjectilePhalanx(
            $colony,
            $this->colonyStorageManager
        );
    }
}
