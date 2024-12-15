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
        private ShipRepositoryInterface $shipRepository,
        private SpacecraftSystemRepositoryInterface $shipSystemRepository,
        private TholianWebRepositoryInterface $tholianWebRepository,
        private StatusBarFactoryInterface $statusBarFactory

    ) {}

    #[Override]
    public function createSystemData(
        SpacecraftSystemTypeEnum $systemType,
        SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory
    ): AbstractSystemData {
        switch ($systemType) {
            case SpacecraftSystemTypeEnum::SYSTEM_HULL:
                return  new HullSystemData(
                    $this->shipSystemRepository,
                    $this->statusBarFactory
                );
            case SpacecraftSystemTypeEnum::SYSTEM_SHIELDS:
                return  new ShieldSystemData(
                    $this->shipSystemRepository,
                    $this->statusBarFactory
                );
            case SpacecraftSystemTypeEnum::SYSTEM_EPS:
                return  new EpsSystemData(
                    $this->shipSystemRepository,
                    $this->statusBarFactory
                );
            case SpacecraftSystemTypeEnum::SYSTEM_TRACKER:
                return  new TrackerSystemData(
                    $this->shipRepository,
                    $spacecraftWrapperFactory,
                    $this->shipSystemRepository,
                    $this->statusBarFactory
                );
            case SpacecraftSystemTypeEnum::SYSTEM_THOLIAN_WEB:
                return  new WebEmitterSystemData(
                    $this->shipSystemRepository,
                    $this->tholianWebRepository,
                    $this->statusBarFactory
                );
            case SpacecraftSystemTypeEnum::SYSTEM_WARPDRIVE:
                return  new WarpDriveSystemData(
                    $this->shipSystemRepository,
                    $this->statusBarFactory
                );
            case SpacecraftSystemTypeEnum::SYSTEM_WARPCORE:
                return  new WarpCoreSystemData(
                    $this->shipSystemRepository,
                    $this->statusBarFactory
                );
            case SpacecraftSystemTypeEnum::SYSTEM_SINGULARITY_REACTOR:
                return  new SingularityCoreSystemData(
                    $this->shipSystemRepository,
                    $this->statusBarFactory
                );
            case SpacecraftSystemTypeEnum::SYSTEM_FUSION_REACTOR:
                return  new FusionCoreSystemData(
                    $this->shipSystemRepository,
                    $this->statusBarFactory
                );
            case SpacecraftSystemTypeEnum::SYSTEM_ASTRO_LABORATORY:
                return  new AstroLaboratorySystemData(
                    $this->shipSystemRepository,
                    $this->statusBarFactory
                );
            case SpacecraftSystemTypeEnum::SYSTEM_TORPEDO:
                return  new ProjectileLauncherSystemData(
                    $this->shipSystemRepository,
                    $this->statusBarFactory
                );
            case SpacecraftSystemTypeEnum::SYSTEM_BUSSARD_COLLECTOR:
                return  new BussardCollectorSystemData(
                    $this->shipSystemRepository,
                    $this->statusBarFactory
                );
            case SpacecraftSystemTypeEnum::SYSTEM_AGGREGATION_SYSTEM:
                return  new AggregationSystemSystemData(
                    $this->shipSystemRepository,
                    $this->statusBarFactory
                );
        }

        throw new InvalidSystemException(sprintf('no system data present for systemType: %d', $systemType->value));
    }
}
