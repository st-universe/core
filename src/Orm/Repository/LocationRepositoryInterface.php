<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\LocationInterface;

/**
 * @extends ObjectRepository<Location>
 *
 * @method null|LocationInterface find(integer $id)
 */
interface LocationRepositoryInterface extends ObjectRepository
{
    /**
     * @return array<LocationInterface>
     */
    public function getForSubspaceEllipseCreation(): array;

    /**
     * @return array<array{category_name: string, amount: int}>
     */
    public function getRumpCategoryInfo(LayerInterface $layer, int $cx, int $cy): array;

    public function getRandomLocation(): LocationInterface;
}
