<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\ModuleCostInterface;

interface ModuleCostRepositoryInterface extends ObjectRepository
{
    /**
     * @return ModuleCostInterface[]
     */
    public function getByModule(int $moduleId): array;
}