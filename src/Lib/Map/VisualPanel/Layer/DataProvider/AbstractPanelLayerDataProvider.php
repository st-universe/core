<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider;

use Doctrine\ORM\Query\ResultSetMapping;
use Override;
use Stu\Lib\Map\VisualPanel\Layer\Data\CellDataInterface;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Orm\Repository\LocationRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

abstract class AbstractPanelLayerDataProvider implements PanelLayerDataProviderInterface
{
    public function __construct(
        protected LocationRepositoryInterface $locationRepository,
        protected MapRepositoryInterface $mapRepository,
        protected StarSystemMapRepositoryInterface $starSystemMapRepository
    ) {
    }

    /**
     * @return string The class name of the entity.
     * @psalm-return class-string
     */
    abstract protected function getDataClassString(): string;

    abstract protected function addFieldResults(ResultSetMapping $rsm): void;

    /** @return array<CellDataInterface> */
    abstract protected function provideDataForMap(PanelBoundaries $boundaries): array;

    /** @return array<CellDataInterface> */
    abstract protected function provideDataForSystemMap(PanelBoundaries $boundaries): array;

    #[Override]
    public function loadData(PanelBoundaries $boundaries): array
    {
        if ($boundaries->isOnMap()) {

            return $this->provideDataForMap($boundaries);
        } else {

            return $this->provideDataForSystemMap($boundaries);
        }
    }

    protected function createResultSetMapping(): ResultSetMapping
    {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult($this->getDataClassString(), 'd');
        $rsm->addFieldResult('d', 'x', 'x');
        $rsm->addFieldResult('d', 'y', 'y');

        $this->addFieldResults($rsm);

        return $rsm;
    }
}
