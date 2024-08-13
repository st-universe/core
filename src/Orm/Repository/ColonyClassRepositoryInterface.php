<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ColonyClass;
use Stu\Orm\Entity\ColonyClassInterface;

/**
 * @extends ObjectRepository<ColonyClass>
 *
 * @method null|ColonyClassInterface find(integer $id)
 */
interface ColonyClassRepositoryInterface extends ObjectRepository
{
    public function save(ColonyClassInterface $obj): void;

    /**
     * @return list<ColonyClassInterface>
     */
    public function getWithoutDatabaseEntry(): array;
}
