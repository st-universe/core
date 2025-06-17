<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider\Border;

use Stu\Lib\Map\VisualPanel\Layer\DataProvider\AbstractPanelLayerDataProvider;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

interface BorderDataProviderFactoryInterface
{
    public function getDataProvider(?SpacecraftWrapperInterface $currentWrapper, ?bool $isOnShipLevel): AbstractPanelLayerDataProvider;
}
