<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider\Spacecraftcount;

use Stu\Lib\Map\VisualPanel\PanelBoundaries;

final class GeneralSpacecraftCountDataProvider extends AbstractShipcountDataProvider
{
    #[\Override]
    protected function provideDataForMap(PanelBoundaries $boundaries): array
    {
        return $this->mapRepository->getSpacecraftCountLayerData($boundaries, $this->createResultSetMapping());
    }

    #[\Override]
    protected function provideDataForSystemMap(PanelBoundaries $boundaries): array
    {
        return $this->starSystemMapRepository->getSpacecraftCountLayerData($boundaries, $this->createResultSetMapping());
    }
}
