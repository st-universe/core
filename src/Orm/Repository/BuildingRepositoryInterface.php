<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\BuildingInterface;
use Stu\Orm\Entity\ResearchInterface;

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
    public function getBuildmenuBuildings(
        PlanetFieldHostInterface $host,
        int $userId,
        int $buildMenu,
        int $offset,
        int $commodityId = null,
        int $fieldType = null
    ): array;

    /** @return array<BuildingInterface> */
    public function getByResearch(ResearchInterface $research): array;
}
