<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Data;

use Stu\Component\Ship\System\Exception\InvalidSystemException;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;

final class ShipSystemDataFactory implements ShipSystemDataFactoryInterface
{
    private ShipRepositoryInterface $shipRepository;

    private ShipSystemRepositoryInterface $shipSystemRepository;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        ShipSystemRepositoryInterface $shipSystemRepository
    ) {
        $this->shipRepository = $shipRepository;
        $this->shipSystemRepository = $shipSystemRepository;
    }

    public function createSystemData(
        int $systemType,
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
                    $this->shipRepository,
                    $this->shipSystemRepository,
                    $shipWrapperFactory
                );
        }

        throw new InvalidSystemException(sprintf('no system data present for systemType: %d', $systemType));
    }
}
