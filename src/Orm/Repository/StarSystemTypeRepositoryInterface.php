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
    public function save(StarSystemTypeInterface $type): void;

    /**
     * @return array<StarSystemTypeInterface>
     */
    public function getWithoutDatabaseEntry(): array;
}
