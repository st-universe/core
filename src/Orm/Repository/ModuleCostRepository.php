<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\ModuleCost;

/**
 * @extends EntityRepository<ModuleCost>
 */
final class ModuleCostRepository extends EntityRepository implements ModuleCostRepositoryInterface
{
    public function getByModule(int $moduleId): array
    {
        return $this->findBy([
            'module_id' => $moduleId
        ]);
    }
}
