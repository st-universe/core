<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\StarSystemInterface;

/**
 * @method null|StarSystemInterface find(integer $id)
 */
interface StarSystemRepositoryInterface extends ObjectRepository
{
    /**
     * @return StarSystemInterface[]
     */
    public function getByLayer(int $layerId): array;

    public function getByCoordinates(int $cx, int $cy): ?StarSystemInterface;

    /**
     * @return StarSystemInterface[]
     */
    public function getWithoutDatabaseEntry(): array;
}
