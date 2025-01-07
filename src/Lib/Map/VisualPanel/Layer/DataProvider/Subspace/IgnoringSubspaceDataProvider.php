<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace;

use Override;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Orm\Repository\LocationRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

final class IgnoringSubspaceDataProvider extends AbstractSubspaceDataProvider
{
    public function __construct(
        private int $ignoreId,
        LocationRepositoryInterface $locationRepository,
        MapRepositoryInterface $mapRepository,
        StarSystemMapRepositoryInterface $starSystemMapRepository
    ) {
        parent::__construct(
            $locationRepository,
            $mapRepository,
            $starSystemMapRepository
        );
    }

    #[Override]
    protected function provideDataForMap(PanelBoundaries $boundaries): array
    {
        return $this->mapRepository->getIgnoringSubspaceLayerData($boundaries, $this->ignoreId, $this->createResultSetMapping());
    }

    #[Override]
    protected function provideDataForSystemMap(PanelBoundaries $boundaries): array
    {
        return $this->starSystemMapRepository->getIgnoringSubspaceLayerData($boundaries, $this->ignoreId, $this->createResultSetMapping());
    }
}
