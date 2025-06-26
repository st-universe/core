<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\StarSystemType;

/**
 * @extends ObjectRepository<StarSystemType>
 *
 * @method StarSystemType[] findAll()
 */
interface StarSystemTypeRepositoryInterface extends ObjectRepository
{
    public function save(StarSystemType $type): void;

    /**
     * @return array<StarSystemType>
     */
    public function getWithoutDatabaseEntry(): array;
}
