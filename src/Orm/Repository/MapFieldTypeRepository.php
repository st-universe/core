<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\MapFieldType;

/**
 * @extends EntityRepository<MapFieldType>
 */
final class MapFieldTypeRepository extends EntityRepository implements MapFieldTypeRepositoryInterface
{
}
