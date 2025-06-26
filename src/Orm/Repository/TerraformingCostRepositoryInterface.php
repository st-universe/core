<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\TerraformingCost;

/**
 * @extends ObjectRepository<TerraformingCost>
 */
interface TerraformingCostRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<TerraformingCost>
     */
    public function getByTerraforming(int $terraformingId): array;
}
