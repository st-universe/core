<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\TorpedoTypeInterface;

/**
 * @method null|TorpedoTypeInterface find(integer $id)
 */
interface TorpedoTypeRepositoryInterface extends ObjectRepository
{
    /**
     * @return TorpedoTypeInterface[]
     */
    public function getAll(): array;

    /**
     * @return TorpedoTypeInterface[]
     */
    public function getForUser(int $userId): array;

    /**
     * @return TorpedoTypeInterface[]
     */
    public function getByLevel(int $level): array;
}
