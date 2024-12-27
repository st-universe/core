<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\PrivateMessage;
use Stu\Orm\Entity\PrivateMessageFolderInterface;
use Stu\Orm\Entity\PrivateMessageInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends ObjectRepository<PrivateMessage>
 *
 * @method null|PrivateMessageInterface find(integer $id)
 */
interface PrivateMessageRepositoryInterface extends ObjectRepository
{
    public function prototype(): PrivateMessageInterface;

    public function save(PrivateMessageInterface $post, bool $doFlush = false): void;

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
    public function getBySender(UserInterface $user): array;

    /**
     * @return array<PrivateMessageInterface>
     */
    public function getByReceiver(UserInterface $user): array;

    /** @return array<PrivateMessageInterface> */
    public function getByUserAndFolder(
        int $userId,
        int $folderId,
        int $offset,
        int $limit
    ): array;

    /** @return array<PrivateMessageInterface> */
    public function getConversations(UserInterface $user): array;

    public function getAmountByFolder(PrivateMessageFolderInterface $privateMessageFolder): int;

    public function getNewAmountByFolder(PrivateMessageFolderInterface $privateMessageFolder): int;

    public function setDeleteTimestampByFolder(int $folderId, int $timestamp): void;

    public function hasRecentMessage(UserInterface $user): bool;

    public function unsetAllInboxReferences(): void;

    public function truncateAllPrivateMessages(): void;
}
