<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\StarSystemType;
use Stu\Orm\Entity\StarSystemTypeInterface;

/**
 * @extends ObjectRepository<StarSystemType>
 * 
 * @method StarSystemTypeInterface[] findAll()
 */
interface StarSystemTypeRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<StarSystemTypeInterface>
     */
    public function getWithoutDatabaseEntry(): array;
}
