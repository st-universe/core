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
    /** @return array<StarSystemMapInterface> */
    public function getBySystemOrdered(int $starSystemId): array;

    public function getByCoordinates(
        int $starSystemId,
        int $sx,
        int $sy
    ): ?StarSystemMapInterface;

    /** @return array<string, StarSystemMapInterface> */
    public function getByBoundaries(PanelBoundaries $boundaries): array;

    /** @return array<string, StarSystemMapInterface> */
    public function getByCoordinateRange(
        int $starSystemId,
        int $startSx,
        int $endSx,
        int $startSy,
        int $endSy,
        bool $sortAscending = true
    ): array;

    /** @return array<CellDataInterface> */
    public function getMapLayerData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array;

    /** @return array<CellDataInterface> */
    public function getSpacecraftCountLayerData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array;

    /** @return array<CellDataInterface> */
    public function getColonyShieldData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array;

    /** @return array<CellDataInterface> */
    public function getNormalBorderData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array;

    /** @return array<CellDataInterface> */
    public function getRegionBorderData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array;

    /** @return array<CellDataInterface> */
    public function getImpassableBorderData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array;

    /** @return array<CellDataInterface> */
    public function getCartographingData(PanelBoundaries $boundaries, ResultSetMapping $rsm, string $locations): array;

    /** @return array<CellDataInterface> */
    public function getAnomalyData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array;

    /** @return array<CellDataInterface> */
    public function getIgnoringSubspaceLayerData(PanelBoundaries $boundaries, int $ignoreUserId, ResultSetMapping $rsm): array;

    /**
     * @return array<int>
     */
    public function getRandomSystemMapIdsForAstroMeasurement(int $starSystemId, int $location): array;

    public function prototype(): StarSystemMapInterface;

    public function save(StarSystemMapInterface $starSystemMap): void;

    public function truncateByStarSystem(StarSystemInterface $starSystem): void;
}