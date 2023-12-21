<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace;

use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

final class IgnoringSubspaceDataProvider extends AbstractSubspaceDataProvider
{
    private int $ignoreId;

    public function __construct( #
        int $ignoreId,
        MapRepositoryInterface $mapRepository,
        StarSystemMapRepositoryInterface $starSystemMapRepository
    ) {
        parent::__construct($mapRepository, $starSystemMapRepository);
        $this->ignoreId = $ignoreId;
    }

    protected function provideDataForMap(PanelBoundaries $boundaries): array
    {
        return $this->mapRepository->getIgnoringSubspaceLayerData($boundaries, $this->ignoreId, $this->createResultSetMapping());
    }

    protected function provideDataForSystemMap(PanelBoundaries $boundaries): array
    {
        return $this->starSystemMapRepository->getIgnoringSubspaceLayerData($boundaries, $this->ignoreId, $this->createResultSetMapping());
    }
}
