<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\PlanetTypeInterface;

/**
 * @method null|PlanetTypeInterface find(integer $id)
 */
interface PlanetTypeRepositoryInterface extends ObjectRepository
{
    /**
     * @return PlanetTypeInterface[]
     */
    public function getWithoutDatabaseEntry(): array;
}
