<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ModuleSpecial;
use Stu\Orm\Entity\ModuleSpecialInterface;

/**
 * @extends ObjectRepository<ModuleSpecial>
 */
interface ModuleSpecialRepositoryInterface extends ObjectRepository
{
    /**
     * @return ModuleSpecialInterface[]
     */
    public function getByModule(int $moduleId): array;
}
