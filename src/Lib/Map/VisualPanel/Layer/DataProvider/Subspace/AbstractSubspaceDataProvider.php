<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace;

use Doctrine\ORM\Query\ResultSetMapping;
use Override;
use Stu\Lib\Map\VisualPanel\Layer\Data\SubspaceData;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\AbstractPanelLayerDataProvider;

abstract class AbstractSubspaceDataProvider extends AbstractPanelLayerDataProvider
{
    #[Override]
    protected function getDataClassString(): string
    {
        return SubspaceData::class;
    }

    #[Override]
    protected function addFieldResults(ResultSetMapping $rsm): void
    {
        $rsm->addFieldResult('d', 'd1c', 'd1c');
        $rsm->addFieldResult('d', 'd2c', 'd2c');
        $rsm->addFieldResult('d', 'd3c', 'd3c');
        $rsm->addFieldResult('d', 'd4c', 'd4c');
        $rsm->addFieldResult('d', 'effects', 'effects');
    }
}
