<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\TerraformingCost;
use Stu\Orm\Entity\TerraformingCostInterface;

/**
 * @extends EntityRepository<TerraformingCost>
 */
final class TerraformingCostRepository extends EntityRepository implements TerraformingCostRepositoryInterface
{
    /**
     * @return TerraformingCostInterface[]
     */
    #[Override]
    public function getByTerraforming(int $terraformingId): array
    {
        return $this->findBy([
            'terraforming_id' => $terraformingId
        ]);
    }
}
