<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider\Shipcount;

use Stu\Lib\Map\VisualPanel\PanelBoundaries;

final class GeneralShipcountDataProvider extends AbstractShipcountDataProvider
{
    protected function provideDataForMap(PanelBoundaries $boundaries): array
    {
        return $this->mapRepository->getShipCountLayerData($boundaries, $this->createResultSetMapping());
    }

    protected function provideDataForSystemMap(PanelBoundaries $boundaries): array
    {
        return $this->starSystemMapRepository->getShipCountLayerData($boundaries, $this->createResultSetMapping());
    }
}
