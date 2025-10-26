<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\TerraformingCost;

/**
 * @extends EntityRepository<TerraformingCost>
 */
final class TerraformingCostRepository extends EntityRepository implements TerraformingCostRepositoryInterface
{
    /**
     * @return TerraformingCost[]
     */
    #[\Override]
    public function getByTerraforming(int $terraformingId): array
    {
        return $this->findBy([
            'terraforming_id' => $terraformingId
        ]);
    }
}
