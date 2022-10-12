<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\PrivateMessageInterface;

/**
 * @method null|PrivateMessageInterface find(integer $id)
 */
interface PrivateMessageRepositoryInterface extends ObjectRepository
{
    public function prototype(): PrivateMessageInterface;

    public function save(PrivateMessageInterface $post): void;

    public function delete(PrivateMessageInterface $post): void;

    /**
     * @return PrivateMessageInterface[]
     */
    public function getOrderedCorrepondence(
        int $senderUserId,
        int $recipientUserId,
        int $limit
    ): iterable;

    /**
     * @return PrivateMessageInterface[]
     */
    public function getBySender(int $userId): iterable;

    /**
     * @return PrivateMessageInterface[]
     */
    public function getByUserAndFolder(
        int $userId,
        int $folderId,
        int $offset,
        int $limit
    ): iterable;

    public function getAmountByFolder(int $folderId): int;

    public function getNewAmountByFolder(int $folderId): int;

    public function truncateByFolder(int $folderId): void;
}