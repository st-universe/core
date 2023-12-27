<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider\Shipcount;

use Stu\Lib\Map\VisualPanel\Layer\DataProvider\AbstractPanelLayerDataProvider;

interface ShipcountDataProviderFactoryInterface
{
    public function getDataProvider(int $id, ShipcountLayerTypeEnum $type): AbstractPanelLayerDataProvider;
}
