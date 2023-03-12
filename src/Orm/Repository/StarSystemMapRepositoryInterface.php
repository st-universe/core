<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
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
     * @return list<StarSystemMapInterface>
     */
    public function getBySystemOrdered(int $starSystemId): array;

    public function getByCoordinates(
        int $starSystemId,
        int $sx,
        int $sy
    ): ?StarSystemMapInterface;

    /**
     * @return list<StarSystemMapInterface>
     */
    public function getByCoordinateRange(
        StarSystemInterface $starSystem,
        int $startSx,
        int $endSx,
        int $startSy,
        int $endSy
    ): array;

    /**
     * @return list<StarSystemMapInterface>
     */
    public function getRandomFieldsForAstroMeasurement(int $starSystemId): array;

    /**
     * @return list<array{category_name: string, amount: int}>
     */
    public function getRumpCategoryInfo(int $cx, int $cy): array;

    public function save(StarSystemMapInterface $starSystemMap): void;
}
