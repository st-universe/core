<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\TorpedoTypeInterface;

interface TorpedoTypeRepositoryInterface extends ObjectRepository
{
    /**
     * @return TorpedoTypeInterface[]
     */
    public function getForUser(int $userId): array;

    /**
     * @return TorpedoTypeInterface[]
     */
    public function getByLevel(int $level): array;
}