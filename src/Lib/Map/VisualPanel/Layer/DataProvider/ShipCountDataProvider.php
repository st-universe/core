<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider;

use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Lib\Map\VisualPanel\Layer\Data\ShipCountData;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;

final class ShipCountDataProvider extends AbstractPanelLayerDataProvider
{
    protected function getDataClassString(): string
    {
        return ShipCountData::class;
    }

    protected function addFieldResults(ResultSetMapping $rsm): void
    {
        $rsm->addFieldResult('d', 'shipcount', 'shipcount');
        $rsm->addFieldResult('d', 'cloakcount', 'cloakcount');
    }

    protected function provideDataForMap(PanelBoundaries $boundaries): array
    {
        return $this->mapRepository->getShipCountLayerData($boundaries, $this->createResultSetMapping());
    }

    protected function provideDataForSystemMap(PanelBoundaries $boundaries): array
    {
        return $this->starSystemMapRepository->getShipCountLayerData($boundaries, $this->createResultSetMapping());
    }
}
