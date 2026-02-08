<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\PlanetFieldType;

/**
 * @extends EntityRepository<PlanetFieldType>
 */
final class PlanetFieldTypeRepository extends EntityRepository implements PlanetFieldTypeRepositoryInterface {}
