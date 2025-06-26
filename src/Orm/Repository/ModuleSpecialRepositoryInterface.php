<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ModuleSpecial;

/**
 * @extends ObjectRepository<ModuleSpecial>
 */
interface ModuleSpecialRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<ModuleSpecial>
     */
    public function getByModule(int $moduleId): array;
}
