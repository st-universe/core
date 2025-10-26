<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider;

use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Lib\Map\VisualPanel\Layer\Data\AnomalyData;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;

final class AnomalyDataProvider extends AbstractPanelLayerDataProvider
{
    #[\Override]
    protected function getDataClassString(): string
    {
        return AnomalyData::class;
    }

    #[\Override]
    protected function addFieldResults(ResultSetMapping $rsm): void
    {
        $rsm->addFieldResult('d', 'anomalytypes', 'anomalytypes');
    }

    #[\Override]
    protected function provideDataForMap(PanelBoundaries $boundaries): array
    {
        return $this->mapRepository->getAnomalyData($boundaries, $this->createResultSetMapping());
    }

    #[\Override]
    protected function provideDataForSystemMap(PanelBoundaries $boundaries): array
    {
        return $this->starSystemMapRepository->getAnomalyData($boundaries, $this->createResultSetMapping());
    }
}
