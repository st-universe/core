<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider\Spacecraftcount;

use Stu\Lib\Map\VisualPanel\Layer\DataProvider\AbstractPanelLayerDataProvider;
use Stu\Orm\Repository\LocationRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

final class SpacecraftCountDataProviderFactory implements SpacecraftCountDataProviderFactoryInterface
{
    public function __construct(
        private LocationRepositoryInterface $locationRepository,
        private MapRepositoryInterface $mapRepository,
        private StarSystemMapRepositoryInterface $starSystemMapRepository
    ) {}

    #[\Override]
    public function getDataProvider(int $id, SpacecraftCountLayerTypeEnum $type): AbstractPanelLayerDataProvider
    {

        return match ($type) {
            SpacecraftCountLayerTypeEnum::ALL =>
            new GeneralSpacecraftCountDataProvider($this->locationRepository, $this->mapRepository, $this->starSystemMapRepository),
            SpacecraftCountLayerTypeEnum::ALLIANCE_ONLY =>
            new AllianceSpacecraftCountDataProvider($id, $this->locationRepository, $this->mapRepository, $this->starSystemMapRepository),
            SpacecraftCountLayerTypeEnum::USER_ONLY =>
            new UserSpacecraftCountDataProvider($id, $this->locationRepository, $this->mapRepository, $this->starSystemMapRepository),
            SpacecraftCountLayerTypeEnum::SPACECRAFT_ONLY =>
            new SpacecraftSpacecraftCountDataProvider($id, $this->locationRepository, $this->mapRepository, $this->starSystemMapRepository),
        };
    }
}
