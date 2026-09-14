<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Stu\Module\Game\Component\GameComponentEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Orm\Entity\PrivateMessage;
use Stu\Orm\Entity\PrivateMessageFolder;
use Stu\Orm\Entity\User;

/**
 * @extends EntityRepository<PrivateMessage>
 */
final class PrivateMessageRepository extends EntityRepository implements PrivateMessageRepositoryInterface
{
    #[\Override]
    public function prototype(): PrivateMessage
    {
        return new PrivateMessage();
    }

    #[\Override]
    public function save(PrivateMessage $post, bool $doFlush = false): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);

        if ($doFlush) {
            $em->flush();
        }
    }

    #[\Override]
    public function getOrderedCorrepondence(
        int $userId,
        int $otherUserId,
        array $specialIds,
        int $limit
    ): array {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT pm FROM %s pm
                    JOIN %s pmf
                    WITH pm.cat_id = pmf.id
                    WHERE ( (pm.send_user = :userId
                                AND pm.recip_user = :otherUserId)
                            OR
                            (pm.send_user = :otherUserId
                                AND pm.recip_user = :userId
                                AND pm.deleted IS NULL))
                    AND pmf.special in (:specialIds)
                    ORDER BY pm.date DESC',
                    PrivateMessage::class,
                    PrivateMessageFolder::class
                )
            )
            ->setParameters([
                'userId' => $userId,
                'otherUserId' => $otherUserId,
                'specialIds' => $specialIds
            ])
            ->setMaxResults($limit)
            ->getResult();
    }

    #[\Override]
    public function getBySender(User $user): array
    {
        return $this->findBy(
            ['send_user' => $user->getId()]
        );
    }

    #[\Override]
    public function getByReceiver(User $user): array
    {
        return $this->findBy(
            ['recip_user' => $user->getId()]
        );
    }

    #[\Override]
    public function getByUserAndFolder(
        int $userId,
        int $folderId,
        int $offset,
        int $limit
    ): array {
        return $this->findBy(
            ['recip_user' => $userId, 'cat_id' => $folderId, 'deleted' => null],
            ['date' => 'desc', 'id' => 'desc'],
            $limit,
            $offset
        );
    }

    #[\Override]
    public function getAmountByFolder(PrivateMessageFolder $privateMessageFolder): int
    {
        return $this->count([
            'category' => $privateMessageFolder,
            'deleted' => null
        ]);
    }

    #[\Override]
    public function getNewAmountByFolder(PrivateMessageFolder $privateMessageFolder): int
    {
        return $this->count([
            'category' => $privateMessageFolder,
            'new' => 1,
            'deleted' => null
        ]);
    }

    #[\Override]
    public function getNewAmountByFolderAndSender(
        PrivateMessageFolder $privateMessageFolder,
        User $sender
    ): int {
        return $this->count([
            'category' => $privateMessageFolder,
            'sendingUser' => $sender,
            'new' => 1,
            'deleted' => null
        ]);
    }

    #[\Override]
    public function setDeleteTimestampByFolder(int $folderId, int $timestamp): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'UPDATE %s pm SET pm.deleted = :timestamp WHERE pm.cat_id = :folderId',
                    PrivateMessage::class
                )
            )
            ->setParameters([
                'folderId' => $folderId,
                'timestamp' => $timestamp
            ])
            ->execute();
    }

    #[\Override]
    public function markAsReadByFolder(int $folderId): int
    {
        return (int) $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'UPDATE %s pm
                    SET pm.new = :read
                    WHERE pm.cat_id = :folderId
                    AND pm.new = :unread
                    AND pm.deleted IS NULL',
                    PrivateMessage::class
                )
            )
            ->setParameters([
                'folderId' => $folderId,
                'read' => false,
                'unread' => true
            ])
            ->execute();
    }

    #[\Override]
    public function hasRecentMessage(User $user): bool
    {
        return
            (int) $this->getEntityManager()
                ->createQuery(
                    sprintf(
                        'SELECT count(pm.id)
                    FROM %s pm
                    WHERE pm.receivingUser = :user
                    AND pm.new = :true
                    AND pm.date > :threshold',
                        PrivateMessage::class
                    )
                )
                ->setParameters([
                    'user' => $user,
                    'threshold' => time() - GameComponentEnum::PM->getRefreshIntervalInSeconds(),
                    'true' => true
                ])
                ->getSingleScalarResult() > 0
        ;
    }

    #[\Override]
    public function getConversations(User $user, int $offset = 0, int $limit = 20): array
    {
        $query = $this->getConversationsQuery($user)
            ->setFirstResult(max(0, $offset))
            ->setMaxResults(max(1, $limit));
        $paginator = new Paginator($query);

        return [
            'items' => iterator_to_array($paginator),
            'total' => count($paginator),
        ];
    }

    /**
     * @return Query<int, PrivateMessage>
     */
    private function getConversationsQuery(User $user): Query
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT pm FROM %1$s pm
                JOIN pm.category pmf
                LEFT JOIN pm.inboxPm inbox
                LEFT JOIN inbox.category pmfinbox
                WHERE pmf.special in (:main, :out)
                AND (pmfinbox.special is null or pmfinbox.special = :main)
                AND pm.receivingUser = :user
                AND pm.deleted IS NULL
                AND (pm.send_user != :system
                    OR (pm.former_send_user IS NOT NULL AND pm.former_send_user != :system))
                AND NOT EXISTS (SELECT pm2.id FROM %1$s pm2
                    JOIN pm2.category pmf2
                    LEFT JOIN pm2.inboxPm inbox2
                    LEFT JOIN inbox2.category pmfinbox2
                    WHERE pmf2.special in (:main, :out)
                    AND (pmfinbox2.special is null or pmfinbox2.special = :main)
                    AND pm2.receivingUser = :user
                    AND pm2.deleted IS NULL
                    AND (pm2.send_user != :system
                        OR (pm2.former_send_user IS NOT NULL AND pm2.former_send_user != :system))
                    AND (CASE WHEN pm2.send_user = :system
                        THEN pm2.former_send_user ELSE pm2.send_user END) =
                        (CASE WHEN pm.send_user = :system
                        THEN pm.former_send_user ELSE pm.send_user END)
                    AND (pm2.date > pm.date
                        OR (pm2.date = pm.date AND pm2.id > pm.id)))
                ORDER BY pm.date DESC, pm.id DESC',
                    PrivateMessage::class
                )
            )
            ->setParameters([
                'user' => $user,
                'main' => PrivateMessageFolderTypeEnum::SPECIAL_MAIN,
                'out' => PrivateMessageFolderTypeEnum::SPECIAL_PMOUT,
                'system' => 1
            ]);
    }

    #[\Override]
    public function getAmountSince(int $timestamp): int
    {
        return (int) $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT count(pm.id)
                    FROM %s pm
                    JOIN %s pmf
                    WITH pm.category = pmf
                    WHERE pm.date > :threshold
                    AND pmf.special != :outbox',
                    PrivateMessage::class,
                    PrivateMessageFolder::class
                )
            )
            ->setParameters([
                'threshold' => $timestamp,
                'outbox' => PrivateMessageFolderTypeEnum::SPECIAL_PMOUT
            ])
            ->getSingleScalarResult();
    }

    #[\Override]
    public function unsetAllInboxReferences(): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'UPDATE %s pm
                SET pm.inbox_pm_id = null',
                    PrivateMessage::class
                )
            )
            ->execute();
    }
}
