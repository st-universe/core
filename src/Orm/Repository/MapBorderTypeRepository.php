<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\MapBorderType;

/**
 * @extends EntityRepository<MapBorderType>
 */
final class MapBorderTypeRepository extends EntityRepository implements MapBorderTypeRepositoryInterface {}
