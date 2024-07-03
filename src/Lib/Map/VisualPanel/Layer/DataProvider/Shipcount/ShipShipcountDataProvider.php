<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider\Shipcount;

use Override;
use Crunz\Exception\NotImplementedException;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Shipcount\AbstractShipcountDataProvider;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

final class ShipShipcountDataProvider extends AbstractShipcountDataProvider
{
    public function __construct( #
        private int $shipId,
        MapRepositoryInterface $mapRepository,
        StarSystemMapRepositoryInterface $starSystemMapRepository
    ) {
        parent::__construct($mapRepository, $starSystemMapRepository);
    }

    #[Override]
    protected function provideDataForMap(PanelBoundaries $boundaries): array
    {
        return $this->mapRepository->getShipShipcountLayerData($boundaries, $this->shipId, $this->createResultSetMapping());
    }

    #[Override]
    protected function provideDataForSystemMap(PanelBoundaries $boundaries): array
    {
        throw new NotImplementedException('this is not possible');
    }
}
