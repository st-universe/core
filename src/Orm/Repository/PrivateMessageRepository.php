<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\PrivateMessage;
use Stu\Orm\Entity\PrivateMessageFolder;
use Stu\Orm\Entity\PrivateMessageFolderInterface;
use Stu\Orm\Entity\PrivateMessageInterface;

/**
 * @extends EntityRepository<PrivateMessage>
 */
final class PrivateMessageRepository extends EntityRepository implements PrivateMessageRepositoryInterface
{
    public function prototype(): PrivateMessageInterface
    {
        return new PrivateMessage();
    }

    public function save(PrivateMessageInterface $post, bool $doFlush = false): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);

        if ($doFlush) {
            $em->flush();
        }
    }

    public function delete(PrivateMessageInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush();
    }

    public function getOrderedCorrepondence(
        int $senderUserId,
        int $recipientUserId,
        array $specialIds,
        int $limit
    ): array {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT pm FROM %s pm
                    JOIN %s pmf
                    WITH pm.cat_id = pmf.id
                    WHERE ((pm.send_user = :sendUserId AND pm.recip_user = :recipUserId) OR
                        (pm.send_user = :recipUserId AND pm.recip_user = :sendUserId))
                    AND pmf.special in (:specialIds)
                    AND pm.deleted IS NULL
                    ORDER BY pm.date DESC',
                    PrivateMessage::class,
                    PrivateMessageFolder::class
                )
            )
            ->setParameters([
                'sendUserId' => $senderUserId,
                'recipUserId' => $recipientUserId,
                'specialIds' => $specialIds
            ])
            ->setMaxResults($limit)
            ->getResult();
    }

    public function getBySender(int $userId): array
    {
        return $this->findBy(
            ['send_user' => $userId]
        );
    }

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

    public function getAmountByFolder(PrivateMessageFolderInterface $privateMessageFolder): int
    {
        return $this->count([
            'category' => $privateMessageFolder,
            'deleted' => null
        ]);
    }

    public function getNewAmountByFolder(PrivateMessageFolderInterface $privateMessageFolder): int
    {
        return $this->count([
            'category' => $privateMessageFolder,
            'new' => 1,
            'deleted' => null
        ]);
    }

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
