<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace;

use Crunz\Exception\NotImplementedException;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

final class UserSubspaceDataProvider extends AbstractSubspaceDataProvider
{
    private int $userId;

    public function __construct( #
        int $userId,
        MapRepositoryInterface $mapRepository,
        StarSystemMapRepositoryInterface $starSystemMapRepository
    ) {
        parent::__construct($mapRepository, $starSystemMapRepository);
        $this->userId = $userId;
    }

    protected function provideDataForMap(PanelBoundaries $boundaries): array
    {
        return $this->mapRepository->getUserSubspaceLayerData($boundaries, $this->userId, $this->createResultSetMapping());
    }

    protected function provideDataForSystemMap(PanelBoundaries $boundaries): array
    {
        throw new NotImplementedException('this is not possible');
    }
}
