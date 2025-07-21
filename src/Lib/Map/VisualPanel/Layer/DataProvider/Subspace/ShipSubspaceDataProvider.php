<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace;

use Override;
use Stu\Lib\Map\VisualPanel\Layer\Data\SpacecraftSignatureData;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Orm\Repository\LocationRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

final class ShipSubspaceDataProvider extends AbstractSubspaceDataProvider
{
    public function __construct(
        private int $shipId,
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
    protected function getDataClassString(): string
    {
        return SpacecraftSignatureData::class;
    }

    #[Override]
    protected function provideDataForMap(PanelBoundaries $boundaries): array
    {
        return $this->mapRepository->getShipSubspaceLayerData($boundaries, $this->shipId, $this->createResultSetMapping());
    }

    #[Override]
    protected function provideDataForSystemMap(PanelBoundaries $boundaries): array
    {
        return $this->starSystemMapRepository->getShipSubspaceLayerData($boundaries, $this->shipId, $this->createResultSetMapping());
    }
}
