<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\Location;

/**
 * @extends ObjectRepository<Location>
 *
 * @method null|Location find(integer $id)
 */
interface LocationRepositoryInterface extends ObjectRepository
{
    /**
     * @return array<Location>
     */
    public function getForSubspaceEllipseCreation(): array;

    /**
     * @return array<array{category_name: string, amount: int}>
     */
    public function getRumpCategoryInfo(Layer $layer, int $cx, int $cy): array;

    public function getRandomLocation(): Location;

    public function getByCoordinates(int $x, int $y, int $layerId): ?Location;
}
