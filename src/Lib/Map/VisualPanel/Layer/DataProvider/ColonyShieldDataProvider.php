<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider;

use Crunz\Exception\NotImplementedException;
use Doctrine\ORM\Query\ResultSetMapping;
use Override;
use Stu\Lib\Map\VisualPanel\Layer\Data\ColonyShieldData;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;

final class ColonyShieldDataProvider extends AbstractPanelLayerDataProvider
{
    #[Override]
    protected function getDataClassString(): string
    {
        return ColonyShieldData::class;
    }

    #[Override]
    protected function addFieldResults(ResultSetMapping $rsm): void
    {
        $rsm->addFieldResult('d', 'shieldstate', 'shieldstate');
    }

    #[Override]
    protected function provideDataForMap(PanelBoundaries $boundaries): array
    {
        throw new NotImplementedException('this is not possible');
    }

    #[Override]
    protected function provideDataForSystemMap(PanelBoundaries $boundaries): array
    {
        return $this->starSystemMapRepository->getColonyShieldData($boundaries, $this->createResultSetMapping());
    }
}
