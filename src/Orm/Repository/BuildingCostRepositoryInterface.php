<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;

interface BuildingCostRepositoryInterface extends ObjectRepository
{
    public function getByBuilding(int $buildingId): array;
}