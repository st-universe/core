<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider;

use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Lib\Map\VisualPanel\Layer\Data\BorderData;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;

final class BorderDataProvider extends AbstractPanelLayerDataProvider
{
    protected function getDataClassString(): string
    {
        return BorderData::class;
    }

    protected function addFieldResults(ResultSetMapping $rsm): void
    {
        $rsm->addFieldResult('d', 'allycolor', 'allycolor');
        $rsm->addFieldResult('d', 'usercolor', 'usercolor');
        $rsm->addFieldResult('d', 'factioncolor', 'factioncolor');
    }

    protected function provideDataForMap(PanelBoundaries $boundaries): array
    {
        return $this->mapRepository->getBorderData($boundaries, $this->createResultSetMapping());
    }

    protected function provideDataForSystemMap(PanelBoundaries $boundaries): array
    {
        return $this->starSystemMapRepository->getBorderData($boundaries, $this->createResultSetMapping());
    }
}
