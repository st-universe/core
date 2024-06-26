<?php

namespace Stu\Orm\Repository;

use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ObjectRepository;
use Stu\Lib\Map\VisualPanel\Layer\Data\CellDataInterface;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\StarSystemMapInterface;

/**
 * @extends ObjectRepository<StarSystemMap>
 *
 * @method null|StarSystemMapInterface find(integer $id)
 */
interface StarSystemMapRepositoryInterface extends ObjectRepository
{
    /**
     * @return array<StarSystemMapInterface>
     */
    public function getBySystemOrdered(int $starSystemId): array;

    public function getByCoordinates(
        int $starSystemId,
        int $sx,
        int $sy
    ): ?StarSystemMapInterface;

    /**
     * @return array<StarSystemMapInterface>
     */
    public function getByCoordinateRange(
        StarSystemInterface $starSystem,
        int $startSx,
        int $endSx,
        int $startSy,
        int $endSy,
        bool $sortAscending = true
    ): array;

    /** @return array<CellDataInterface> */
    public function getMapLayerData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array;

    /** @return array<CellDataInterface> */
    public function getShipCountLayerData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array;

    /** @return array<CellDataInterface> */
    public function getColonyShieldData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array;

    /** @return array<CellDataInterface> */
    public function getBorderData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array;

    /** @return array<CellDataInterface> */
    public function getIgnoringSubspaceLayerData(PanelBoundaries $boundaries, int $ignoreId, ResultSetMapping $rsm): array;

    /**
     * @return array<int>
     */
    public function getRandomSystemMapIdsForAstroMeasurement(int $starSystemId): array;

    /**
     * @return array<array{category_name: string, amount: int}>
     */
    public function getRumpCategoryInfo(LayerInterface $layer, int $cx, int $cy): array;

    public function prototype(): StarSystemMapInterface;

    public function save(StarSystemMapInterface $starSystemMap): void;

    /**
     * @return array<StarSystemMapInterface>
     */
    public function getForSubspaceEllipseCreation(): array;

    public function truncateByStarSystem(StarSystemInterface $starSystem): void;
}
