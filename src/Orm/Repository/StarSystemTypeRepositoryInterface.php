<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\StarSystemTypeInterface;

interface StarSystemTypeRepositoryInterface extends ObjectRepository
{
    /**
     * @return StarSystemTypeInterface[]
     */
    public function getWithoutDatabaseEntry(): array;
}