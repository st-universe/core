<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ColonyClassRestriction;
use Stu\Orm\Entity\ColonyClassRestrictionInterface;
use Stu\Orm\Entity\ColonyClassInterface;
use Stu\Orm\Entity\TerraformingInterface;
use Stu\Orm\Entity\BuildingInterface;

/**
 * @extends ObjectRepository<ColonyClassRestriction>
 *
 * @method null|ColonyClassRestrictionInterface find(integer $id)
 */
interface ColonyClassRestrictionRepositoryInterface extends ObjectRepository
{
    public function prototype(): ColonyClassRestrictionInterface;

    public function save(ColonyClassRestrictionInterface $restriction): void;

    public function delete(ColonyClassRestrictionInterface $restriction): void;
}