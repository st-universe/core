<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Weapon;
use Stu\Orm\Entity\WeaponInterface;

/**
 * @extends ObjectRepository<Weapon>
 */
interface WeaponRepositoryInterface extends ObjectRepository
{
    public function findByModule(int $moduleId): ?WeaponInterface;
}
