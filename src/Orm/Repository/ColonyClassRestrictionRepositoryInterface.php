<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ColonyClassRestriction;

/**
 * @extends ObjectRepository<ColonyClassRestriction>
 *
 * @method null|ColonyClassRestriction find(integer $id)
 */
interface ColonyClassRestrictionRepositoryInterface extends ObjectRepository
{
    public function prototype(): ColonyClassRestriction;

    public function save(ColonyClassRestriction $restriction): void;

    public function delete(ColonyClassRestriction $restriction): void;
}
