<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider\Border;

use Doctrine\ORM\Query\ResultSetMapping;
use Override;
use Stu\Lib\Map\VisualPanel\Layer\Data\BorderData;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Orm\Repository\LocationRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;


final class NormalBorderDataProvider extends AbstractBorderDataProvider
{
    public function __construct(
        protected LocationRepositoryInterface $locationRepository,
        protected MapRepositoryInterface $mapRepository,
        protected StarSystemMapRepositoryInterface $starSystemMapRepository
    ) {}

    #[Override]
    protected function getDataClassString(): string
    {
        return BorderData::class;
    }

    #[Override]
    protected function addFieldResults(ResultSetMapping $rsm): void
    {
        $rsm->addFieldResult('d', 'normal', 'normal');
    }


    #[Override]
    protected function provideDataForMap(PanelBoundaries $boundaries): array
    {
        return $this->mapRepository->getNormalBorderData($boundaries, $this->createResultSetMapping());
    }

    #[Override]
    protected function provideDataForSystemMap(PanelBoundaries $boundaries): array
    {
        return $this->starSystemMapRepository->getNormalBorderData($boundaries, $this->createResultSetMapping());
    }
}