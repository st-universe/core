<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\ShipRumpCategory;

/**
 * @extends EntityRepository<ShipRumpCategory>
 */
final class ShipRumpCategoryRepository extends EntityRepository implements ShipRumpCategoryRepositoryInterface {}
