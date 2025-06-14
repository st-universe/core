<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider\Border;

use Override;
use Stu\Component\Spacecraft\SpacecraftLssModeEnum;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\AbstractPanelLayerDataProvider;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Repository\LocationRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;
use Stu\Orm\Repository\AstroEntryRepositoryInterface;

final class BorderDataProviderFactory implements BorderDataProviderFactoryInterface
{
    public function __construct(
        private LocationRepositoryInterface $locationRepository,
        private MapRepositoryInterface $mapRepository,
        private StarSystemMapRepositoryInterface $starSystemMapRepository,
        private AstroEntryRepositoryInterface $astroEntryRepository
    ) {}

    #[Override]
    public function getDataProvider(?SpacecraftWrapperInterface $currentWrapper, ?bool $isOnShipLevel): AbstractPanelLayerDataProvider
    {
        if ($currentWrapper === null) {
            return $this->createNormalBorderDataProvider();
        }

        $lss = $currentWrapper->getLssSystemData();
        if ($lss === null) {
            return $this->createNormalBorderDataProvider();
        }

        $currentSpacecraft = $currentWrapper->get();

        return match ($lss->getMode()) {
            SpacecraftLssModeEnum::NORMAL =>
            $this->createNormalBorderDataProvider(),
            SpacecraftLssModeEnum::BORDER =>
            new RegionBorderDataProvider($this->locationRepository, $this->mapRepository, $this->starSystemMapRepository),
            SpacecraftLssModeEnum::IMPASSABLE =>
            new ImpassableBorderDataProvider($currentSpacecraft, $this->mapRepository, $this->starSystemMapRepository),
            SpacecraftLssModeEnum::CARTOGRAPHING =>
            new CartographyBorderDataProvider($currentSpacecraft, $this->mapRepository, $this->starSystemMapRepository, $this->astroEntryRepository)
        };
    }

    private function createNormalBorderDataProvider(): NormalBorderDataProvider
    {
        return new NormalBorderDataProvider(
            $this->locationRepository,
            $this->mapRepository,
            $this->starSystemMapRepository
        );
    }
}
