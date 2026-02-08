<?php

namespace Stu\Orm\Repository;

use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ObjectRepository;
use Stu\Lib\Map\VisualPanel\Layer\Data\CellDataInterface;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Orm\Entity\StarSystem;
use Stu\Orm\Entity\StarSystemMap;

/**
 * @extends ObjectRepository<StarSystemMap>
 *
 * @method null|StarSystemMap find(integer $id)
 */
interface StarSystemMapRepositoryInterface extends ObjectRepository
{
    /** @return array<StarSystemMap> */
    public function getBySystemOrdered(int $starSystemId): array;

    public function getByCoordinates(
        int $starSystemId,
        int $sx,
        int $sy
    ): ?StarSystemMap;

    /** @return array<string, StarSystemMap> */
    public function getByBoundaries(PanelBoundaries $boundaries): array;

    /** @return array<string, StarSystemMap> */
    public function getByCoordinateRange(
        int $starSystemId,
        int $startSx,
        int $endSx,
        int $startSy,
        int $endSy,
        bool $sortAscending = true
    ): array;

    /** @return array< array{x: int, y: int, effects: ?string}> */
    public function getLssBlockadeLocations(PanelBoundaries $boundaries): array;

    /** @return array<CellDataInterface> */
    public function getMapLayerData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array;

    /** @return array<CellDataInterface> */
    public function getSpacecraftCountLayerData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array;
    /** @return array<CellDataInterface> */
    public function getShipSubspaceLayerData(PanelBoundaries $boundaries, int $shipId, int $time, ResultSetMapping $rsm, ?int $rumpId = null): array;

    /** @return array<CellDataInterface> */
    public function getColonyShieldData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array;

    /** @return array<CellDataInterface> */
    public function getNormalBorderData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array;

    /** @return array<CellDataInterface> */
    public function getRegionBorderData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array;

    /** @return array<CellDataInterface> */
    public function getImpassableBorderData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array;

    /**
     * @param array<int> $locations
     * @return array<CellDataInterface>
     */
    public function getCartographingData(PanelBoundaries $boundaries, ResultSetMapping $rsm, array $locations): array;

    /** @return array<CellDataInterface> */
    public function getAnomalyData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array;

    /** @return array<CellDataInterface> */
    public function getIgnoringSubspaceLayerData(PanelBoundaries $boundaries, int $ignoreUserId, int $time, ResultSetMapping $rsm): array;

    /**
     * @return array<int>
     */
    public function getRandomSystemMapIdsForAstroMeasurement(int $starSystemId, int $location): array;

    public function prototype(): StarSystemMap;

    public function save(StarSystemMap $starSystemMap): void;

    public function truncateByStarSystem(StarSystem $starSystem): void;
}
