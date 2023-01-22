<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\StarSystem;
use Stu\Orm\Entity\StarSystemInterface;

/**
 * @extends ObjectRepository<StarSystem>
 *
 * @method null|StarSystemInterface find(integer $id)
 */
interface StarSystemRepositoryInterface extends ObjectRepository
{
    /**
     * @return StarSystemInterface[]
     */
    public function getByLayer(int $layerId): array;

    /**
     * @return StarSystemInterface[]
     */
    public function getWithoutDatabaseEntry(): array;
}
