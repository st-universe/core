<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ModuleCost;
use Stu\Orm\Entity\ModuleCostInterface;

/**
 * @extends ObjectRepository<ModuleCost>
 */
interface ModuleCostRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<ModuleCostInterface>
     */
    public function getByModule(int $moduleId): array;
}
