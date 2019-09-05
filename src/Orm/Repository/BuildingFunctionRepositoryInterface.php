<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\BuildingFunctionInterface;

interface BuildingFunctionRepositoryInterface extends ObjectRepository
{
    /**
     * @return BuildingFunctionInterface[]
     */
    public function getByBuilding(int $buildingId): array;
}