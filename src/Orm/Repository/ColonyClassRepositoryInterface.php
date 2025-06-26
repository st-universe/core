<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ColonyClass;

/**
 * @extends ObjectRepository<ColonyClass>
 *
 * @method null|ColonyClass find(integer $id)
 */
interface ColonyClassRepositoryInterface extends ObjectRepository
{
    public function save(ColonyClass $obj): void;

    /**
     * @return list<ColonyClass>
     */
    public function getWithoutDatabaseEntry(): array;
}
