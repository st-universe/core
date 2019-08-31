<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;

interface TerraformingCostRepositoryInterface extends ObjectRepository
{
    public function getByTerraforming(int $terraformingId): array;
}