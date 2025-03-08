<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider\Border;

use Doctrine\ORM\Query\ResultSetMapping;
use Override;
use Stu\Lib\Map\VisualPanel\Layer\Data\BorderData;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\AbstractPanelLayerDataProvider;

abstract class AbstractBorderDataProvider extends AbstractPanelLayerDataProvider
{
    #[Override]
    protected function getDataClassString(): string
    {
        return BorderData::class;
    }

    #[Override]
    protected function addFieldResults(ResultSetMapping $rsm): void
    {
        $rsm->addFieldResult('d', 'impassable', 'impassable');
        $rsm->addFieldResult('d', 'cartographing', 'cartographing');
        $rsm->addFieldResult('d', 'complementary_color', 'complementary_color');
    }
}
