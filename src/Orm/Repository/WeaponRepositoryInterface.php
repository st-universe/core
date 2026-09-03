<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Weapon;

/**
 * @extends ObjectRepository<Weapon>
 */
interface WeaponRepositoryInterface extends ObjectRepository {}
