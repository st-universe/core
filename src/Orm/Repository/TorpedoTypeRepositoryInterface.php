<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\TorpedoType;

/**
 * @extends ObjectRepository<TorpedoType>
 *
 * @method null|TorpedoType find(integer $id)
 */
interface TorpedoTypeRepositoryInterface extends ObjectRepository
{
    /**
     * @return array<int, TorpedoType>
     */
    public function getAll(): array;

    /**
     * @return array<int, TorpedoType>
     */
    public function getForUser(int $userId): array;

    /**
     * @return array<int, TorpedoType>
     */
    public function getByLevel(int $level): array;
}
