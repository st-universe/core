<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\Weapon;

/**
 * @extends EntityRepository<Weapon>
 */
final class WeaponRepository extends EntityRepository implements WeaponRepositoryInterface {}
