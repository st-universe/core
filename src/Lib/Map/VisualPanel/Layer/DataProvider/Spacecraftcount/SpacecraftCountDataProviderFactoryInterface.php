<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider\Spacecraftcount;

use Stu\Lib\Map\VisualPanel\Layer\DataProvider\AbstractPanelLayerDataProvider;

interface SpacecraftCountDataProviderFactoryInterface
{
    public function getDataProvider(int $id, SpacecraftCountLayerTypeEnum $type): AbstractPanelLayerDataProvider;
}
