<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider\Border;

use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Lib\Map\VisualPanel\Layer\Data\BorderData;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;

final class RegionBorderDataProvider extends AbstractBorderDataProvider
{
    #[\Override]
    protected function getDataClassString(): string
    {
        return BorderData::class;
    }

    #[\Override]
    protected function addFieldResults(ResultSetMapping $rsm): void
    {
        $rsm->addFieldResult('d', 'allycolor', 'allycolor');
        $rsm->addFieldResult('d', 'usercolor', 'usercolor');
        $rsm->addFieldResult('d', 'factioncolor', 'factioncolor');
    }

    #[\Override]
    protected function provideDataForMap(PanelBoundaries $boundaries): array
    {
        return $this->mapRepository->getRegionBorderData($boundaries, $this->createResultSetMapping());
    }

    #[\Override]
    protected function provideDataForSystemMap(PanelBoundaries $boundaries): array
    {
        return $this->starSystemMapRepository->getRegionBorderData($boundaries, $this->createResultSetMapping());
    }
}