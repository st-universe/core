<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ColonyClassInterface;

/**
 * @method null|ColonyClassInterface find(integer $id)
 */
interface ColonyClassRepositoryInterface extends ObjectRepository
{
    /**
     * @return ColonyClassInterface[]
     */
    public function getWithoutDatabaseEntry(): array;
}
