<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\TorpedoStorage;

/**
 * @extends ObjectRepository<TorpedoStorage>
 *
 * @method null|TorpedoStorage find(integer $id)
 */
interface TorpedoStorageRepositoryInterface extends ObjectRepository
{
    public function prototype(): TorpedoStorage;

    public function save(TorpedoStorage $torpedoStorage): void;

    public function delete(TorpedoStorage $torpedoStorage): void;
}
