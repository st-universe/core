<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace;

use Override;
use Stu\Lib\Map\VisualPanel\Layer\Data\SpacecraftSignatureData;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Module\Control\StuTime;
use Stu\Orm\Repository\LocationRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

final class ShipSubspaceDataProvider extends AbstractSubspaceDataProvider
{
    public function __construct(
        private int $shipId,
        private readonly StuTime $stuTime,
        LocationRepositoryInterface $locationRepository,
        MapRepositoryInterface $mapRepository,
        StarSystemMapRepositoryInterface $starSystemMapRepository,
        private ?int $rumpId = null
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
        return $this->mapRepository->getShipSubspaceLayerData(
            $boundaries,
            $this->shipId,
            $this->stuTime->time(),
            $this->createResultSetMapping(),
            true,
            $this->rumpId
        );
    }

    #[Override]
    protected function provideDataForSystemMap(PanelBoundaries $boundaries): array
    {
        return $this->starSystemMapRepository->getShipSubspaceLayerData(
            $boundaries,
            $this->shipId,
            $this->stuTime->time(),
            $this->createResultSetMapping(),
            $this->rumpId
        );
    }
}
