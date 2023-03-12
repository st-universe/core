<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\BuildingInterface;

/**
 * @extends ObjectRepository<Building>
 *
 * @method null|BuildingInterface find(integer $id)
 */
interface BuildingRepositoryInterface extends ObjectRepository
{
    /**
     * @return iterable<array{id: int, name: string}>
     */
    public function getByColonyAndUserAndBuildMenu(
        int $colonyId,
        int $userId,
        int $buildMenu,
        int $offset
    ): iterable;
}
