<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider\Shipcount;

use RuntimeException;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\AbstractPanelLayerDataProvider;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

final class ShipcountDataProviderFactory implements ShipcountDataProviderFactoryInterface
{
    private MapRepositoryInterface $mapRepository;

    private StarSystemMapRepositoryInterface $starSystemMapRepository;

    public function __construct(
        MapRepositoryInterface $mapRepository,
        StarSystemMapRepositoryInterface $starSystemMapRepository
    ) {
        $this->mapRepository = $mapRepository;
        $this->starSystemMapRepository = $starSystemMapRepository;
    }

    public function getDataProvider(int $id, ShipcountLayerTypeEnum $type): AbstractPanelLayerDataProvider
    {
        switch ($type) {
            case ShipcountLayerTypeEnum::ALL:
                return new GeneralShipcountDataProvider($this->mapRepository, $this->starSystemMapRepository);
            case ShipcountLayerTypeEnum::ALLIANCE_ONLY:
                return new AllianceShipcountDataProvider($id, $this->mapRepository, $this->starSystemMapRepository);
            case ShipcountLayerTypeEnum::USER_ONLY:
                return new UserShipcountDataProvider($id, $this->mapRepository, $this->starSystemMapRepository);
        }

        throw new RuntimeException(sprintf('Shipcount layer type %d is not supported', $type->value));
    }
}
