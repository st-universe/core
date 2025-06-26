<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ModuleCost;

/**
 * @extends ObjectRepository<ModuleCost>
 */
interface ModuleCostRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<ModuleCost>
     */
    public function getByModule(int $moduleId): array;
}
