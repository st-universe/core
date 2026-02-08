<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\ShipRumpRole;

/**
 * @extends EntityRepository<ShipRumpRole>
 */
final class ShipRumpRoleRepository extends EntityRepository implements ShipRumpRoleRepositoryInterface {}
