<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\PlanetFieldType;

/**
 * @extends ObjectRepository<PlanetFieldType>
 *
 * @method PlanetFieldType[] findAll()
 */
interface PlanetFieldTypeRepositoryInterface extends ObjectRepository {}
