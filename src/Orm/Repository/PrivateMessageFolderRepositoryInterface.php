<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\PrivateMessageFolderInterface;

/**
 * @method null|PrivateMessageFolderInterface find(integer $id)
 */
interface PrivateMessageFolderRepositoryInterface extends ObjectRepository
{
    public function prototype(): PrivateMessageFolderInterface;

    public function save(PrivateMessageFolderInterface $post): void;

    public function delete(PrivateMessageFolderInterface $post): void;

    /**
     * @return PrivateMessageFolderInterface[]
     */
    public function getOrderedByUser(int $userId): iterable;

    public function getByUserAndSpecial(int $userId, int $specialId): ?PrivateMessageFolderInterface;

    public function getMaxOrderIdByUser(int $userId): int;
}