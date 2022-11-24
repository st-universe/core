<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\MapInterface;

/**
 * @method null|MapInterface find(integer $id)
 */
interface MapRepositoryInterface extends ObjectRepository
{
    public function count(array $criteria);

    /**
     * @return MapInterface[]
     */
    public function getAllOrdered(): array;

    /**
     * @return MapInterface[]
     */
    public function getAllWithSystem(): array;

    /**
     * @return MapInterface[]
     */
    public function getAllWithoutSystem(): array;

    public function getByCoordinates(int $cx, int $cy): ?MapInterface;

    /**
     * @return MapInterface[]
     */
    public function getByCoordinateRange(
        int $startSx,
        int $endSx,
        int $startSy,
        int $endSy
    ): array;

    public function save(MapInterface $map): void;

    public function getExplored(int $userId, int $startX, int $endX, int $cy): array;

    public function getRandomPassableUnoccupiedWithoutDamage(): int;
}
