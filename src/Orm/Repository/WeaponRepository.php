<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\Weapon;
use Stu\Orm\Entity\WeaponInterface;

/**
 * @extends EntityRepository<Weapon>
 */
final class WeaponRepository extends EntityRepository implements WeaponRepositoryInterface
{
    public function findByModule(int $moduleId): ?WeaponInterface
    {
        return $this->findOneBy([
            'module_id' => $moduleId
        ]);
    }
}
