<?php

namespace Stu\Orm\Repository;

use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ObjectRepository;
use Stu\Lib\Map\VisualPanel\Layer\Data\CellDataInterface;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Module\Starmap\Lib\ExploreableStarMapInterface;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<Map>
 *
 * @method null|Map find(integer $id)
 */
interface MapRepositoryInterface extends ObjectRepository
{
    public function getAmountByLayer(Layer $layer): int;

    /**
     * @return array<Map>
     */
    public function getAllOrdered(int $layerId): array;

    /**
     * @return array<int, Map>
     */
    public function getAllWithSystem(int $layerId): array;

    /**
     * @return array<int, Map>
     */
    public function getAllWithoutSystem(int $layerId): array;

    public function getByCoordinates(?Layer $layer, int $cx, int $cy): ?Map;

    /** @return array<string, Map> */
    public function getByBoundaries(PanelBoundaries $boundaries): array;

    /** @return array<string, Map> */
    public function getByCoordinateRange(
        int $layerId,
        int $startSx,
        int $endSx,
        int $startSy,
        int $endSy,
        bool $sortAscending = true
    ): array;

    public function save(Map $map): void;

    /** @return array< array{x: int, y: int, effects: ?string}> */
    public function getLssBlockadeLocations(PanelBoundaries $boundaries): array;

    /** @return array<CellDataInterface> */
    public function getMapLayerData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array;

    /** @return array<CellDataInterface> */
    public function getNormalBorderData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array;

    /**
     * @param array<int> $locations
     * @return array<CellDataInterface>
     */
    public function getCartographingData(PanelBoundaries $boundaries, ResultSetMapping $rsm, array $locations): array;

    /** @return array<CellDataInterface> */
    public function getRegionBorderData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array;

    /** @return array<CellDataInterface> */
    public function getImpassableBorderData(PanelBoundaries $boundaries, User $user, ResultSetMapping $rsm): array;

    /** @return array<CellDataInterface> */
    public function getAnomalyData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array;

    /** @return array<CellDataInterface> */
    public function getSpacecraftCountLayerData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array;

    /** @return array<CellDataInterface> */
    public function getAllianceSpacecraftCountLayerData(PanelBoundaries $boundaries, int $allianceId, ResultSetMapping $rsm): array;

    /** @return array<CellDataInterface> */
    public function getUserSpacecraftCountLayerData(PanelBoundaries $boundaries, int $userId, ResultSetMapping $rsm): array;

    /** @return array<CellDataInterface> */
    public function getSpacecraftCountLayerDataForSpacecraft(PanelBoundaries $boundaries, int $spacecraftId, ResultSetMapping $rsm): array;

    /**
     * @return array<ExploreableStarMapInterface>
     */
    public function getExplored(
        int $userId,
        int $layerId,
        int $startX,
        int $endX,
        int $cy
    ): array;

    /**
     * @return array<Map>
     */
    public function getWithEmptySystem(Layer $layer): array;


    /**
     * @return array<int>
     */
    public function getRandomMapIdsForAstroMeasurement(int $regionId, int $maxPercentage, int $location): array;


    public function getRandomPassableUnoccupiedWithoutDamage(Layer $layer, bool $isAtBorder = false): Map;

    /** @return array<CellDataInterface> */
    public function getSubspaceLayerData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array;

    /** @return array<CellDataInterface> */
    public function getIgnoringSubspaceLayerData(PanelBoundaries $boundaries, int $ignoreUserId, ResultSetMapping $rsm): array;

    /** @return array<CellDataInterface> */
    public function getAllianceSubspaceLayerData(PanelBoundaries $boundaries, int $allianceId, ResultSetMapping $rsm): array;

    /** @return array<CellDataInterface> */
    public function getUserSubspaceLayerData(PanelBoundaries $boundaries, int $userId, ResultSetMapping $rsm): array;
    /** @return array<CellDataInterface> */
    public function getShipSubspaceLayerData(PanelBoundaries $boundaries, int $shipId, ResultSetMapping $rsm, bool $cloaked_check = false, ?int $rumpId = null): array;

    /** @return array<int> */
    public function getUniqueInfluenceAreaIds(int $layerId): array;

    public function isAdminRegionUserRegion(int $locationId, int $factionId): bool;
}
