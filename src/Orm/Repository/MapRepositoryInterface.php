<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\MapInterface;

/**
 * @method null|MapInterface find(integer $id)
 */
interface MapRepositoryInterface extends ObjectRepository
{
    public function getAmountByLayer(int $layerId): int;

    /**
     * @return MapInterface[]
     */
    public function getAllOrdered(int $layerId): array;

    /**
     * @return MapInterface[]
     */
    public function getAllWithSystem(int $layerId): array;

    /**
     * @return MapInterface[]
     */
    public function getAllWithoutSystem(int $layerId): array;

    public function getByCoordinates(int $layerId, int $cx, int $cy): ?MapInterface;

    /**
     * @return MapInterface[]
     */
    public function getByCoordinateRange(
        int $layerId,
        int $startSx,
        int $endSx,
        int $startSy,
        int $endSy
    ): array;

    public function save(MapInterface $map): void;

    public function getExplored(int $userId, int $layerId, int $startX, int $endX, int $cy): array;

    public function getRandomPassableUnoccupiedWithoutDamage(): int;
}
