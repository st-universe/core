<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\TerraformingCost;
use Stu\Orm\Entity\TerraformingCostInterface;

/**
 * @extends ObjectRepository<TerraformingCost>
 */
interface TerraformingCostRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<TerraformingCostInterface>
     */
    public function getByTerraforming(int $terraformingId): array;
}
