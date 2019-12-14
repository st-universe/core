<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\IgnoreListInterface;

interface IgnoreListRepositoryInterface extends ObjectRepository
{
    public function prototype(): IgnoreListInterface;

    public function save(IgnoreListInterface $ignoreList): void;

    public function delete(IgnoreListInterface $ignoreList): void;

    /**
     * @return IgnoreListInterface[]
     */
    public function getByRecipient(int $recipientId): array;

    /**
     * @return IgnoreListInterface[]
     */
    public function getByUser(int $userId): array;

    public function exists(int $userId, int $recipientId): bool;

    public function truncateByUser(int $userId): void;
}