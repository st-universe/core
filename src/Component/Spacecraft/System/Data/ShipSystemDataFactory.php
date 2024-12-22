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
            case SpacecraftSystemTypeEnum::HULL:
                return  new HullSystemData(
                    $this->shipSystemRepository,
                    $this->statusBarFactory
                );
            case SpacecraftSystemTypeEnum::SHIELDS:
                return  new ShieldSystemData(
                    $this->shipSystemRepository,
                    $this->statusBarFactory
                );
            case SpacecraftSystemTypeEnum::EPS:
                return  new EpsSystemData(
                    $this->shipSystemRepository,
                    $this->statusBarFactory
                );
            case SpacecraftSystemTypeEnum::TRACKER:
                return  new TrackerSystemData(
                    $this->shipRepository,
                    $spacecraftWrapperFactory,
                    $this->shipSystemRepository,
                    $this->statusBarFactory
                );
            case SpacecraftSystemTypeEnum::THOLIAN_WEB:
                return  new WebEmitterSystemData(
                    $this->shipSystemRepository,
                    $this->tholianWebRepository,
                    $this->statusBarFactory
                );
            case SpacecraftSystemTypeEnum::WARPDRIVE:
                return  new WarpDriveSystemData(
                    $this->shipSystemRepository,
                    $this->statusBarFactory
                );
            case SpacecraftSystemTypeEnum::WARPCORE:
                return  new WarpCoreSystemData(
                    $this->shipSystemRepository,
                    $this->statusBarFactory
                );
            case SpacecraftSystemTypeEnum::SINGULARITY_REACTOR:
                return  new SingularityCoreSystemData(
                    $this->shipSystemRepository,
                    $this->statusBarFactory
                );
            case SpacecraftSystemTypeEnum::FUSION_REACTOR:
                return  new FusionCoreSystemData(
                    $this->shipSystemRepository,
                    $this->statusBarFactory
                );
            case SpacecraftSystemTypeEnum::ASTRO_LABORATORY:
                return  new AstroLaboratorySystemData(
                    $this->shipSystemRepository,
                    $this->statusBarFactory
                );
            case SpacecraftSystemTypeEnum::TORPEDO:
                return  new ProjectileLauncherSystemData(
                    $this->shipSystemRepository,
                    $this->statusBarFactory
                );
            case SpacecraftSystemTypeEnum::BUSSARD_COLLECTOR:
                return  new BussardCollectorSystemData(
                    $this->shipSystemRepository,
                    $this->statusBarFactory
                );
            case SpacecraftSystemTypeEnum::AGGREGATION_SYSTEM:
                return  new AggregationSystemSystemData(
                    $this->shipSystemRepository,
                    $this->statusBarFactory
                );
        }

        throw new InvalidSystemException(sprintf('no system data present for systemType: %d', $systemType->value));
    }
}
