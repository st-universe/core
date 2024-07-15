<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace;

use Crunz\Exception\NotImplementedException;
use Override;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Orm\Repository\LocationRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

final class UserSubspaceDataProvider extends AbstractSubspaceDataProvider
{
    public function __construct( #
        private int $userId,
        LocationRepositoryInterface $locationRepository,
        MapRepositoryInterface $mapRepository,
        StarSystemMapRepositoryInterface $starSystemMapRepository
    ) {
        parent::__construct(
            $locationRepository,
            $mapRepository,
            $starSystemMapRepository
        );
    }

    #[Override]
    protected function provideDataForMap(PanelBoundaries $boundaries): array
    {
        return $this->mapRepository->getUserSubspaceLayerData($boundaries, $this->userId, $this->createResultSetMapping());
    }

    #[Override]
    protected function provideDataForSystemMap(PanelBoundaries $boundaries): array
    {
        throw new NotImplementedException('this is not possible');
    }
}
