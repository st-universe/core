<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;

interface BuildingCostRepositoryInterface extends ObjectRepository
{
    public function getByBuilding(int $buildingId): array;
}