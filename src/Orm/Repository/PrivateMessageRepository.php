<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
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
    #[Override]
    public function prototype(): PrivateMessage
    {
        return new PrivateMessage();
    }

    #[Override]
    public function save(PrivateMessage $post, bool $doFlush = false): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);

        if ($doFlush) {
            $em->flush();
        }
    }

    #[Override]
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

    #[Override]
    public function getBySender(User $user): array
    {
        return $this->findBy(
            ['send_user' => $user->getId()]
        );
    }

    #[Override]
    public function getByReceiver(User $user): array
    {
        return $this->findBy(
            ['recip_user' => $user->getId()]
        );
    }

    #[Override]
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

    #[Override]
    public function getAmountByFolder(PrivateMessageFolder $privateMessageFolder): int
    {
        return $this->count([
            'category' => $privateMessageFolder,
            'deleted' => null
        ]);
    }

    #[Override]
    public function getNewAmountByFolder(PrivateMessageFolder $privateMessageFolder): int
    {
        return $this->count([
            'category' => $privateMessageFolder,
            'new' => 1,
            'deleted' => null
        ]);
    }

    public function getNewAmountByFolderAndSender(PrivateMessageFolder $privateMessageFolder, User $sender): int
    {
        return $this->count([
            'category' => $privateMessageFolder,
            'sendingUser' => $sender,
            'new' => 1,
            'deleted' => null
        ]);
    }

    #[Override]
    public function setDeleteTimestampByFolder(int $folderId, int $timestamp): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'UPDATE %s pm SET pm.deleted = :timestamp WHERE pm.cat_id = :folderId',
                PrivateMessage::class
            )
        )->setParameters([
            'folderId' => $folderId,
            'timestamp' => $timestamp
        ])->execute();
    }

    #[Override]
    public function hasRecentMessage(User $user): bool
    {
        return (int)$this->getEntityManager()->createQuery(
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
            ->getSingleScalarResult() > 0;
    }

    #[Override]
    public function getConversations(User $user): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT pm FROM %1$s pm
                JOIN %2$s pmf
                WITH pm.cat_id = pmf.id
                LEFT JOIN %1$s inbox
                WITH pm.inbox_pm_id = inbox.id
                LEFT JOIN %2$s pmfinbox
                WITH inbox.cat_id = pmfinbox.id
                WHERE pmf.special in (:main, :out)
                AND (pmfinbox.special is null or pmfinbox.special = :main)
                AND pm.receivingUser = :user
                AND pm.deleted IS NULL
                AND  NOT EXISTS (SELECT pm2.id FROM %1$s pm2
                                    WHERE pm2.send_user = pm.send_user
                                    AND pm2.cat_id = :out
                                    AND pm2.recip_user = pm.recip_user 
                                    AND pm2.date < pm.date)
                ORDER BY pm.id DESC',
                PrivateMessage::class,
                PrivateMessageFolder::class
            )
        )
            ->setParameters([
                'user' => $user,
                'main' => PrivateMessageFolderTypeEnum::SPECIAL_MAIN,
                'out' => PrivateMessageFolderTypeEnum::SPECIAL_PMOUT
            ])
            ->getResult();
    }

    #[Override]
    public function getAmountSince(int $timestamp): int
    {

        return (int)$this->getEntityManager()->createQuery(
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

    #[Override]
    public function unsetAllInboxReferences(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'UPDATE %s pm
                SET pm.inbox_pm_id = null',
                PrivateMessage::class
            )
        )->execute();
    }

    #[Override]
    public function truncateAllPrivateMessages(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s pm',
                PrivateMessage::class
            )
        )->execute();
    }
}
