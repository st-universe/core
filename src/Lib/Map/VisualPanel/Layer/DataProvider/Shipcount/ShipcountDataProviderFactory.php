<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider\Shipcount;

use Override;
use RuntimeException;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\AbstractPanelLayerDataProvider;
use Stu\Orm\Repository\LocationRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

final class ShipcountDataProviderFactory implements ShipcountDataProviderFactoryInterface
{
    public function __construct(
        private LocationRepositoryInterface $locationRepository,
        private MapRepositoryInterface $mapRepository,
        private StarSystemMapRepositoryInterface $starSystemMapRepository
    ) {
    }

    #[Override]
    public function getDataProvider(int $id, ShipcountLayerTypeEnum $type): AbstractPanelLayerDataProvider
    {
        switch ($type) {
            case ShipcountLayerTypeEnum::ALL:
                return new GeneralShipcountDataProvider($this->locationRepository, $this->mapRepository, $this->starSystemMapRepository);
            case ShipcountLayerTypeEnum::ALLIANCE_ONLY:
                return new AllianceShipcountDataProvider($id, $this->locationRepository, $this->mapRepository, $this->starSystemMapRepository);
            case ShipcountLayerTypeEnum::USER_ONLY:
                return new UserShipcountDataProvider($id, $this->locationRepository, $this->mapRepository, $this->starSystemMapRepository);
            case ShipcountLayerTypeEnum::SHIP_ONLY:
                return new ShipShipcountDataProvider($id, $this->locationRepository, $this->mapRepository, $this->starSystemMapRepository);
        }

        throw new RuntimeException(sprintf('Shipcount layer type %d is not supported', $type->value));
    }
}
