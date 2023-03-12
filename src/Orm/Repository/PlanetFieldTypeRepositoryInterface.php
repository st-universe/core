<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\PlanetFieldType;
use Stu\Orm\Entity\PlanetFieldTypeInterface;

/**
 * @extends ObjectRepository<PlanetFieldType>
 *
 * @method PlanetFieldTypeInterface[] findAll()
 */
interface PlanetFieldTypeRepositoryInterface extends ObjectRepository
{
}
