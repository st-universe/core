<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\TorpedoStorage;
use Stu\Orm\Entity\TorpedoStorageInterface;

/**
 * @extends ObjectRepository<TorpedoStorage>
 *
 * @method null|TorpedoStorageInterface find(integer $id)
 */
interface TorpedoStorageRepositoryInterface extends ObjectRepository
{
    public function prototype(): TorpedoStorageInterface;

    public function save(TorpedoStorageInterface $torpedoStorage): void;

    public function delete(TorpedoStorageInterface $torpedoStorage): void;
}
