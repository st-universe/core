<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\PrivateMessage;
use Stu\Orm\Entity\PrivateMessageFolder;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<PrivateMessage>
 *
 * @method null|PrivateMessage find(integer $id)
 */
interface PrivateMessageRepositoryInterface extends ObjectRepository
{
    public function prototype(): PrivateMessage;

    public function save(PrivateMessage $post, bool $doFlush = false): void;

    /**
     * @param array<int> $specialIds
     *
     * @return array<PrivateMessage>
     */
    public function getOrderedCorrepondence(
        int $userId,
        int $otherUserId,
        array $specialIds,
        int $limit
    ): array;

    /**
     * @return array<PrivateMessage>
     */
    public function getBySender(User $user): array;

    /**
     * @return array<PrivateMessage>
     */
    public function getByReceiver(User $user): array;

    /** @return array<PrivateMessage> */
    public function getByUserAndFolder(
        int $userId,
        int $folderId,
        int $offset,
        int $limit
    ): array;

    /** @return array<PrivateMessage> */
    public function getConversations(User $user): array;

    public function getAmountByFolder(PrivateMessageFolder $privateMessageFolder): int;

    public function getNewAmountByFolder(PrivateMessageFolder $privateMessageFolder): int;

    public function getNewAmountByFolderAndSender(PrivateMessageFolder $privateMessageFolder, User $sender): int;

    public function setDeleteTimestampByFolder(int $folderId, int $timestamp): void;

    public function hasRecentMessage(User $user): bool;

    public function getAmountSince(int $timestamp): int;

    public function unsetAllInboxReferences(): void;
}
