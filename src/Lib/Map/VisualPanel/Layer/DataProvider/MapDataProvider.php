<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider;

use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Lib\Map\VisualPanel\Layer\Data\MapData;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;

final class MapDataProvider extends AbstractPanelLayerDataProvider
{
    protected function getDataClassString(): string
    {
        return MapData::class;
    }

    protected function addFieldResults(ResultSetMapping $rsm): void
    {
        $rsm->addFieldResult('d', 'type', 'type');
    }

    protected function provideDataForMap(PanelBoundaries $boundaries): array
    {
        return $this->mapRepository->getMapLayerData($boundaries, $this->createResultSetMapping());
    }

    protected function provideDataForSystemMap(PanelBoundaries $boundaries): array
    {
        return $this->starSystemMapRepository->getMapLayerData($boundaries, $this->createResultSetMapping());
    }
}
