<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Data;

use Override;
use Stu\Component\Spacecraft\System\Exception\InvalidSystemException;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Template\StatusBarFactoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;
use Stu\Orm\Repository\TholianWebRepositoryInterface;

final class ShipSystemDataFactory implements ShipSystemDataFactoryInterface
{
    public function __construct(
        private readonly ShipRepositoryInterface $shipRepository,
        private readonly SpacecraftSystemRepositoryInterface $shipSystemRepository,
        private readonly TholianWebRepositoryInterface $tholianWebRepository,
        private readonly StatusBarFactoryInterface $statusBarFactory
    ) {}

    #[Override]
    public function createSystemData(
        SpacecraftSystemTypeEnum $systemType,
        SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory
    ): AbstractSystemData {

        return match ($systemType) {
            SpacecraftSystemTypeEnum::HULL =>  new HullSystemData($this->shipSystemRepository, $this->statusBarFactory),
            SpacecraftSystemTypeEnum::SHIELDS =>  new ShieldSystemData($this->shipSystemRepository, $this->statusBarFactory),
            SpacecraftSystemTypeEnum::EPS =>  new EpsSystemData($this->shipSystemRepository, $this->statusBarFactory),
            SpacecraftSystemTypeEnum::COMPUTER =>  new ComputerSystemData($this->shipSystemRepository, $this->statusBarFactory),
            SpacecraftSystemTypeEnum::TRACKER =>  new TrackerSystemData($this->shipRepository, $spacecraftWrapperFactory, $this->shipSystemRepository, $this->statusBarFactory),
            SpacecraftSystemTypeEnum::THOLIAN_WEB =>  new WebEmitterSystemData($this->shipSystemRepository, $this->tholianWebRepository, $this->statusBarFactory),
            SpacecraftSystemTypeEnum::WARPDRIVE =>  new WarpDriveSystemData($this->shipSystemRepository, $this->statusBarFactory),
            SpacecraftSystemTypeEnum::WARPCORE =>  new WarpCoreSystemData($this->shipSystemRepository, $this->statusBarFactory),
            SpacecraftSystemTypeEnum::SINGULARITY_REACTOR =>  new SingularityCoreSystemData($this->shipSystemRepository, $this->statusBarFactory),
            SpacecraftSystemTypeEnum::FUSION_REACTOR =>  new FusionCoreSystemData($this->shipSystemRepository, $this->statusBarFactory),
            SpacecraftSystemTypeEnum::ASTRO_LABORATORY =>  new AstroLaboratorySystemData($this->shipSystemRepository, $this->statusBarFactory),
            SpacecraftSystemTypeEnum::PHASER =>  new EnergyWeaponSystemData($this->shipSystemRepository, $this->statusBarFactory),
            SpacecraftSystemTypeEnum::TORPEDO =>  new ProjectileLauncherSystemData($this->shipSystemRepository, $this->statusBarFactory),
            SpacecraftSystemTypeEnum::BUSSARD_COLLECTOR =>  new BussardCollectorSystemData($this->shipSystemRepository, $this->statusBarFactory),
            SpacecraftSystemTypeEnum::AGGREGATION_SYSTEM =>  new AggregationSystemSystemData($this->shipSystemRepository, $this->statusBarFactory),
            SpacecraftSystemTypeEnum::LSS =>  new LssSystemData($this->shipSystemRepository, $this->statusBarFactory),
            SpacecraftSystemTypeEnum::SUBSPACE_SCANNER => new SubSpaceSystemData($this->shipSystemRepository, $this->statusBarFactory),

            default => throw new InvalidSystemException(sprintf('no system data present for systemType: %d', $systemType->value))
        };
    }
}
