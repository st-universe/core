<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;

interface TerraformingCostRepositoryInterface extends ObjectRepository
{
    public function getByTerraforming(int $terraformingId): array;
}