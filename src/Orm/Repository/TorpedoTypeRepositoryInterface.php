<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\TorpedoType;
use Stu\Orm\Entity\TorpedoTypeInterface;

/**
 * @extends ObjectRepository<TorpedoType>
 *
 * @method null|TorpedoTypeInterface find(integer $id)
 */
interface TorpedoTypeRepositoryInterface extends ObjectRepository
{
    /**
     * @return array<int, TorpedoTypeInterface>
     */
    public function getAll(): array;

    /**
     * @return array<int, TorpedoTypeInterface>
     */
    public function getForUser(int $userId): array;

    /**
     * @return array<int, TorpedoTypeInterface>
     */
    public function getByLevel(int $level): array;
}
