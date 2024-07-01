<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Data;

use Stu\Component\Ship\System\Exception\InvalidSystemException;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;
use Stu\Orm\Repository\TholianWebRepositoryInterface;

final class ShipSystemDataFactory implements ShipSystemDataFactoryInterface
{

    public function __construct(
        private ShipRepositoryInterface $shipRepository,
        private ShipSystemRepositoryInterface $shipSystemRepository,
        private TholianWebRepositoryInterface $tholianWebRepository
    ) {
    }

    public function createSystemData(
        ShipSystemTypeEnum $systemType,
        ShipWrapperFactoryInterface $shipWrapperFactory
    ): AbstractSystemData {
        switch ($systemType) {
            case ShipSystemTypeEnum::SYSTEM_HULL:
                return  new HullSystemData($this->shipSystemRepository);
            case ShipSystemTypeEnum::SYSTEM_SHIELDS:
                return  new ShieldSystemData($this->shipSystemRepository);
            case ShipSystemTypeEnum::SYSTEM_EPS:
                return  new EpsSystemData($this->shipSystemRepository);
            case ShipSystemTypeEnum::SYSTEM_TRACKER:
                return  new TrackerSystemData(
                    $this->shipRepository,
                    $shipWrapperFactory,
                    $this->shipSystemRepository
                );
            case ShipSystemTypeEnum::SYSTEM_THOLIAN_WEB:
                return  new WebEmitterSystemData(
                    $this->shipSystemRepository,
                    $this->tholianWebRepository
                );
            case ShipSystemTypeEnum::SYSTEM_WARPDRIVE:
                return  new WarpDriveSystemData($this->shipSystemRepository);
            case ShipSystemTypeEnum::SYSTEM_WARPCORE:
                return  new WarpCoreSystemData($this->shipSystemRepository);
            case ShipSystemTypeEnum::SYSTEM_SINGULARITY_REACTOR:
                return  new SingularityCoreSystemData($this->shipSystemRepository);
            case ShipSystemTypeEnum::SYSTEM_FUSION_REACTOR:
                return  new FusionCoreSystemData($this->shipSystemRepository);
            case ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY:
                return  new AstroLaboratorySystemData($this->shipSystemRepository);
            case ShipSystemTypeEnum::SYSTEM_TORPEDO:
                return  new ProjectileLauncherSystemData($this->shipSystemRepository);
        }

        throw new InvalidSystemException(sprintf('no system data present for systemType: %d', $systemType->value));
    }
}
