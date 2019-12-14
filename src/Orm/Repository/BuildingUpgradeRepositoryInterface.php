<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\BuildingUpgradeInterface;

interface BuildingUpgradeRepositoryInterface extends ObjectRepository
{
    /**
     * @return BuildingUpgradeInterface[];
     */
    public function getByBuilding(int $buildingId, int $userId): array;
}