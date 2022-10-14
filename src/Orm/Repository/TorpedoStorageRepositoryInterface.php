<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\TorpedoStorageInterface;

/**
 * @method null|TorpedoStorageInterface find(integer $id)
 */
interface TorpedoStorageRepositoryInterface extends ObjectRepository
{
    public function prototype(): TorpedoStorageInterface;

    public function save(TorpedoStorageInterface $torpedoStorage): void;

    public function delete(TorpedoStorageInterface $torpedoStorage): void;
}
