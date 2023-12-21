<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider;

use Stu\Lib\Map\VisualPanel\Layer\Data\CellDataInterface;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;

interface PanelLayerDataProviderInterface
{
    /** @return array<CellDataInterface> */
    public function loadData(PanelBoundaries $boundaries): array;
}
