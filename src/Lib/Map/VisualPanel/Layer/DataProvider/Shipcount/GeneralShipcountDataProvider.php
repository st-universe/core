<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider\Shipcount;

use Override;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;

final class GeneralShipcountDataProvider extends AbstractShipcountDataProvider
{
    #[Override]
    protected function provideDataForMap(PanelBoundaries $boundaries): array
    {
        return $this->mapRepository->getShipCountLayerData($boundaries, $this->createResultSetMapping());
    }

    #[Override]
    protected function provideDataForSystemMap(PanelBoundaries $boundaries): array
    {
        return $this->starSystemMapRepository->getShipCountLayerData($boundaries, $this->createResultSetMapping());
    }
}
