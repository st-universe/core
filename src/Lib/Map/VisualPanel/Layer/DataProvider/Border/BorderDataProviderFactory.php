<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider\Border;

use Override;
use RuntimeException;
use Stu\Component\Spacecraft\SpacecraftLssModeEnum;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\AbstractPanelLayerDataProvider;
use Stu\Orm\Repository\LocationRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;
use Stu\Orm\Entity\SpacecraftInterface;
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
    public function getDataProvider(?SpacecraftInterface $currentSpacecraft, ?bool $isOnShipLevel): AbstractPanelLayerDataProvider
    {

        if ($currentSpacecraft != null) {

            return match ($currentSpacecraft->getLssMode()) {
                SpacecraftLssModeEnum::NORMAL =>
                new NormalBorderDataProvider($this->locationRepository, $this->mapRepository, $this->starSystemMapRepository),
                SpacecraftLssModeEnum::BORDER =>
                new RegionBorderDataProvider($this->locationRepository, $this->mapRepository, $this->starSystemMapRepository),
                SpacecraftLssModeEnum::IMPASSABLE =>
                new ImpassableBorderDataProvider($currentSpacecraft, $this->mapRepository, $this->starSystemMapRepository),
                SpacecraftLssModeEnum::CARTOGRAPHING =>
                new CartographyBorderDataProvider($currentSpacecraft, $this->mapRepository, $this->starSystemMapRepository, $this->astroEntryRepository)
            };
        } else {
            return new NormalBorderDataProvider($this->locationRepository, $this->mapRepository, $this->starSystemMapRepository);
        }
    }
}
