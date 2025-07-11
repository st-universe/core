<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Component\Building\BuildMenuEnum;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\Research;

/**
 * @extends ObjectRepository<Building>
 *
 * @method null|Building find(integer $id)
 */
interface BuildingRepositoryInterface extends ObjectRepository
{
    /**
     * @return array<Building>
     */
    public function getBuildmenuBuildings(
        PlanetFieldHostInterface $host,
        int $userId,
        BuildMenuEnum $buildMenu,
        int $offset,
        ?int $commodityId = null,
        ?int $fieldType = null
    ): array;

    /** @return array<Building> */
    public function getByResearch(Research $research): array;
}
