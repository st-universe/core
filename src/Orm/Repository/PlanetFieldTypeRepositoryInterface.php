<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\PlanetFieldTypeInterface;

/**
 * @method PlanetFieldTypeInterface[] findAll()
 */
interface PlanetFieldTypeRepositoryInterface extends ObjectRepository
{
}
