<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Lib\Colony\PlanetFieldHostInterface;
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
     * @return array<BuildingInterface>
     */
    public function getByColonyAndUserAndBuildMenu(
        PlanetFieldHostInterface $host,
        int $userId,
        int $buildMenu,
        int $offset
    ): array;
}
