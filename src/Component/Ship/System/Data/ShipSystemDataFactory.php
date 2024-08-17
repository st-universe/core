<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Data;

use Override;
use Stu\Component\Ship\System\Exception\InvalidSystemException;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Template\StatusBarFactoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;
use Stu\Orm\Repository\TholianWebRepositoryInterface;

final class ShipSystemDataFactory implements ShipSystemDataFactoryInterface
{
    public function __construct(
        private ShipRepositoryInterface $shipRepository,
        private ShipSystemRepositoryInterface $shipSystemRepository,
        private TholianWebRepositoryInterface $tholianWebRepository,
        private StatusBarFactoryInterface $statusBarFactory
    ) {}

    #[Override]
    public function createSystemData(
        ShipSystemTypeEnum $systemType,
        ShipWrapperFactoryInterface $shipWrapperFactory
    ): AbstractSystemData {
        switch ($systemType) {
            case ShipSystemTypeEnum::SYSTEM_HULL:
                return  new HullSystemData(
                    $this->shipSystemRepository,
                    $this->statusBarFactory
                );
            case ShipSystemTypeEnum::SYSTEM_SHIELDS:
                return  new ShieldSystemData(
                    $this->shipSystemRepository,
                    $this->statusBarFactory
                );
            case ShipSystemTypeEnum::SYSTEM_EPS:
                return  new EpsSystemData(
                    $this->shipSystemRepository,
                    $this->statusBarFactory
                );
            case ShipSystemTypeEnum::SYSTEM_TRACKER:
                return  new TrackerSystemData(
                    $this->shipRepository,
                    $shipWrapperFactory,
                    $this->shipSystemRepository,
                    $this->statusBarFactory
                );
            case ShipSystemTypeEnum::SYSTEM_THOLIAN_WEB:
                return  new WebEmitterSystemData(
                    $this->shipSystemRepository,
                    $this->tholianWebRepository,
                    $this->statusBarFactory
                );
            case ShipSystemTypeEnum::SYSTEM_WARPDRIVE:
                return  new WarpDriveSystemData(
                    $this->shipSystemRepository,
                    $this->statusBarFactory
                );
            case ShipSystemTypeEnum::SYSTEM_WARPCORE:
                return  new WarpCoreSystemData(
                    $this->shipSystemRepository,
                    $this->statusBarFactory
                );
            case ShipSystemTypeEnum::SYSTEM_SINGULARITY_REACTOR:
                return  new SingularityCoreSystemData(
                    $this->shipSystemRepository,
                    $this->statusBarFactory
                );
            case ShipSystemTypeEnum::SYSTEM_FUSION_REACTOR:
                return  new FusionCoreSystemData(
                    $this->shipSystemRepository,
                    $this->statusBarFactory
                );
            case ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY:
                return  new AstroLaboratorySystemData(
                    $this->shipSystemRepository,
                    $this->statusBarFactory
                );
            case ShipSystemTypeEnum::SYSTEM_TORPEDO:
                return  new ProjectileLauncherSystemData(
                    $this->shipSystemRepository,
                    $this->statusBarFactory
                );
        }

        throw new InvalidSystemException(sprintf('no system data present for systemType: %d', $systemType->value));
    }
}
