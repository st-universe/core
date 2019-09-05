<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\StarSystemInterface;

interface StarSystemRepositoryInterface extends ObjectRepository
{
    public function getByCoordinates(int $cx, int $cy): ?StarSystemInterface;

    /**
     * @return StarSystemInterface[]
     */
    public function getWithoutDatabaseEntry(): array;
}