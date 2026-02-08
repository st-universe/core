<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\DatabaseType;

/**
 * @extends EntityRepository<DatabaseType>
 */
final class DatabaseTypeRepository extends EntityRepository implements DatabaseTypeRepositoryInterface {}
