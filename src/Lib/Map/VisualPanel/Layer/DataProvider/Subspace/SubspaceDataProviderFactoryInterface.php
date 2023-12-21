<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace;

use Stu\Lib\Map\VisualPanel\Layer\DataProvider\AbstractPanelLayerDataProvider;

interface SubspaceDataProviderFactoryInterface
{
    public function getDataProvider(int $id, SubspaceLayerTypeEnum $type): AbstractPanelLayerDataProvider;
}
