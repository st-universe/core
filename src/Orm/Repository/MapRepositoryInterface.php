<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Module\Starmap\Lib\ExploreableStarMapInterface;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\MapInterface;

/**
 * @extends ObjectRepository<Map>
 *
 * @method null|MapInterface find(integer $id)
 */
interface MapRepositoryInterface extends ObjectRepository
{
    public function getAmountByLayer(LayerInterface $layer): int;

    /**
     * @return array<MapInterface>
     */
    public function getAllOrdered(int $layerId): array;

    /**
     * @return array<int, MapInterface>
     */
    public function getAllWithSystem(int $layerId): array;

    /**
     * @return array<int, MapInterface>
     */
    public function getAllWithoutSystem(int $layerId): array;

    public function getByCoordinates(int $layerId, int $cx, int $cy): ?MapInterface;

    /**
     * @return array<MapInterface>
     */
    public function getByCoordinateRange(
        int $layerId,
        int $startSx,
        int $endSx,
        int $startSy,
        int $endSy,
        bool $sortAscending = true
    ): array;

    public function save(MapInterface $map): void;

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
     * @return array<MapInterface>
     */
    public function getForSubspaceEllipseCreation(): array;

    /**
     * @return array<MapInterface>
     */
    public function getWithEmptySystem(LayerInterface $layer): array;


    /**
     * @return array<int>
     */
    public function getRandomMapIdsForAstroMeasurement(int $regionId, int $maxPercentage): array;
}
