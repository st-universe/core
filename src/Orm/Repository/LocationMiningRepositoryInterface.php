<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\LocationMining;
use Stu\Orm\Entity\LocationMiningInterface;

/**
 * @extends ObjectRepository<LocationMining>
 *
 * @method null|LocationMiningInterface find(integer $id)
 */
interface LocationMiningRepositoryInterface extends ObjectRepository
{
    public function prototype(): LocationMiningInterface;

    public function save(LocationMiningInterface $locationMining): void;
}
