<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\TerraformingCostInterface;

final class TerraformingCostRepository extends EntityRepository implements TerraformingCostRepositoryInterface
{
    /**
     * @return TerraformingCostInterface[]
     */
    public function getByTerraforming(int $terraformingId): array
    {
        return $this->findBy([
            'terraforming_id' => $terraformingId
        ]);
    }
}