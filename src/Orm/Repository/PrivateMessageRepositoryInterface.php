<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\PrivateMessage;
use Stu\Orm\Entity\PrivateMessageFolderInterface;
use Stu\Orm\Entity\PrivateMessageInterface;

/**
 * @extends ObjectRepository<PrivateMessage>
 *
 * @method null|PrivateMessageInterface find(integer $id)
 */
interface PrivateMessageRepositoryInterface extends ObjectRepository
{
    public function prototype(): PrivateMessageInterface;

    public function save(PrivateMessageInterface $post): void;

    public function delete(PrivateMessageInterface $post): void;

    /**
     * @param array<int> $specialIds
     *
     * @return array<PrivateMessageInterface>
     */
    public function getOrderedCorrepondence(
        int $senderUserId,
        int $recipientUserId,
        array $specialIds,
        int $limit
    ): array;

    /**
     * @return array<PrivateMessageInterface>
     */
    public function getBySender(int $userId): array;

    /**
     * @return array<PrivateMessageInterface>
     */
    public function getByUserAndFolder(
        int $userId,
        int $folderId,
        int $offset,
        int $limit
    ): array;

    public function getAmountByFolder(PrivateMessageFolderInterface $privateMessageFolder): int;

    public function getNewAmountByFolder(PrivateMessageFolderInterface $privateMessageFolder): int;

    public function setDeleteTimestampByFolder(int $folderId, int $timestamp): void;

    public function truncateAllPrivateMessages(): void;
}
