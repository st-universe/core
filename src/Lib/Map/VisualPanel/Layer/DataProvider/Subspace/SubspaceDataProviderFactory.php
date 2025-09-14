<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace;

use Override;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\AbstractPanelLayerDataProvider;
use Stu\Module\Control\StuTime;
use Stu\Orm\Repository\LocationRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

final class SubspaceDataProviderFactory implements SubspaceDataProviderFactoryInterface
{
    public function __construct(
        private readonly LocationRepositoryInterface $locationRepository,
        private readonly MapRepositoryInterface $mapRepository,
        private readonly StarSystemMapRepositoryInterface $starSystemMapRepository,
        private readonly StuTime $stuTime
    ) {}
    #[Override]
    public function getDataProvider(int $id, SubspaceLayerTypeEnum $type, ?int $rumpId = null): AbstractPanelLayerDataProvider
    {
        return match ($type) {
            SubspaceLayerTypeEnum::ALL => new GeneralSubspaceDataProvider($this->locationRepository, $this->mapRepository, $this->starSystemMapRepository),
            SubspaceLayerTypeEnum::IGNORE_USER => new IgnoringSubspaceDataProvider($id, $this->stuTime, $this->locationRepository, $this->mapRepository, $this->starSystemMapRepository),
            SubspaceLayerTypeEnum::ALLIANCE_ONLY => new AllianceSubspaceDataProvider($id, $this->locationRepository, $this->mapRepository, $this->starSystemMapRepository),
            SubspaceLayerTypeEnum::USER_ONLY => new UserSubspaceDataProvider($id, $this->locationRepository, $this->mapRepository, $this->starSystemMapRepository),
            SubspaceLayerTypeEnum::SPACECRAFT_ONLY => new ShipSubspaceDataProvider($id, $this->stuTime, $this->locationRepository, $this->mapRepository, $this->starSystemMapRepository, $rumpId)
        };
    }
}
