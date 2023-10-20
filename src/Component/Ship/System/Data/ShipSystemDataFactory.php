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
    private ShipRepositoryInterface $shipRepository;

    private ShipSystemRepositoryInterface $shipSystemRepository;

    private TholianWebRepositoryInterface $tholianWebRepository;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        ShipSystemRepositoryInterface $shipSystemRepository,
        TholianWebRepositoryInterface $tholianWebRepository
    ) {
        $this->shipRepository = $shipRepository;
        $this->shipSystemRepository = $shipSystemRepository;
        $this->tholianWebRepository = $tholianWebRepository;
    }

    public function createSystemData(
        ShipSystemTypeEnum $systemType,
        ShipWrapperFactoryInterface $shipWrapperFactory
    ): AbstractSystemData {
        switch ($systemType) {
            case ShipSystemTypeEnum::SYSTEM_HULL:
                return  new HullSystemData();
            case ShipSystemTypeEnum::SYSTEM_SHIELDS:
                return  new ShieldSystemData();
            case ShipSystemTypeEnum::SYSTEM_EPS:
                return  new EpsSystemData($this->shipSystemRepository);
            case ShipSystemTypeEnum::SYSTEM_TRACKER:
                return  new TrackerSystemData(
                    $this->shipRepository,
                    $this->shipSystemRepository,
                    $shipWrapperFactory
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
        }

        throw new InvalidSystemException(sprintf('no system data present for systemType: %d', $systemType->value));
    }
}
