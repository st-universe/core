<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\StarSystemTypeInterface;

interface StarSystemTypeRepositoryInterface extends ObjectRepository
{
    /**
     * @return StarSystemTypeInterface[]
     */
    public function getWithoutDatabaseEntry(): array;
}