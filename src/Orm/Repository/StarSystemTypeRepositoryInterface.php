<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\StarSystemType;
use Stu\Orm\Entity\StarSystemTypeInterface;

/**
 * @extends ObjectRepository<StarSystemType>
 */
interface StarSystemTypeRepositoryInterface extends ObjectRepository
{
    /**
     * @return StarSystemTypeInterface[]
     */
    public function getWithoutDatabaseEntry(): array;
}
