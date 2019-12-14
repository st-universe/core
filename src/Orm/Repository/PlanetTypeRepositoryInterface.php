<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\PlanetTypeInterface;

interface PlanetTypeRepositoryInterface extends ObjectRepository
{
    /**
     * @return PlanetTypeInterface[]
     */
    public function getWithoutDatabaseEntry(): array;
}