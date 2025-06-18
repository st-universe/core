<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider\Border;

use Stu\Lib\Map\VisualPanel\Layer\DataProvider\AbstractPanelLayerDataProvider;
use Stu\Orm\Entity\SpacecraftInterface;

interface BorderDataProviderFactoryInterface
{
    public function getDataProvider(?SpacecraftInterface $currentSpacecraft, ?bool $isOnShipLevel): AbstractPanelLayerDataProvider;
}