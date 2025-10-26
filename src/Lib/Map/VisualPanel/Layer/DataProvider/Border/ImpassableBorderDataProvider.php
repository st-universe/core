<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider\Border;

use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Lib\Map\VisualPanel\Layer\Data\BorderData;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

final class ImpassableBorderDataProvider extends AbstractBorderDataProvider
{

    public function __construct(
        private Spacecraft $currentSpacecraft,
        protected MapRepositoryInterface $mapRepository,
        protected StarSystemMapRepositoryInterface $starSystemMapRepository
    ) {}

    #[\Override]
    protected function getDataClassString(): string
    {
        return BorderData::class;
    }

    #[\Override]
    protected function addFieldResults(ResultSetMapping $rsm): void
    {
        $rsm->addFieldResult('d', 'impassable', 'impassable');
        $rsm->addFieldResult('d', 'complementary_color', 'complementary_color');
    }

    #[\Override]
    protected function provideDataForMap(PanelBoundaries $boundaries): array
    {
        return $this->mapRepository->getImpassableBorderData($boundaries, $this->currentSpacecraft->getUser(), $this->createResultSetMapping());
    }

    #[\Override]
    protected function provideDataForSystemMap(PanelBoundaries $boundaries): array
    {
        return $this->starSystemMapRepository->getImpassableBorderData($boundaries, $this->createResultSetMapping());
    }
}
