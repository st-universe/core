<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\MapRegion;

/**
 * @extends EntityRepository<MapRegion>
 */
final class MapRegionRepository extends EntityRepository implements MapRegionRepositoryInterface {}
