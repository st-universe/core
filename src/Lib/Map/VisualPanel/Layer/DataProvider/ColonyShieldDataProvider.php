<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider;

use Crunz\Exception\NotImplementedException;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Lib\Map\VisualPanel\Layer\Data\ColonyShieldData;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;

final class ColonyShieldDataProvider extends AbstractPanelLayerDataProvider
{
    protected function getDataClassString(): string
    {
        return ColonyShieldData::class;
    }

    protected function addFieldResults(ResultSetMapping $rsm): void
    {
        $rsm->addFieldResult('d', 'shieldstate', 'shieldstate');
    }

    protected function provideDataForMap(PanelBoundaries $boundaries): array
    {
        throw new NotImplementedException('this is not possible');
    }

    protected function provideDataForSystemMap(PanelBoundaries $boundaries): array
    {
        return $this->starSystemMapRepository->getColonyShieldData($boundaries, $this->createResultSetMapping());
    }
}
