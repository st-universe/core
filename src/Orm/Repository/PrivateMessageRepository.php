<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\PrivateMessage;
use Stu\Orm\Entity\PrivateMessageFolder;
use Stu\Orm\Entity\PrivateMessageInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;

final class PrivateMessageRepository extends EntityRepository implements PrivateMessageRepositoryInterface
{
    public function prototype(): PrivateMessageInterface
    {
        return new PrivateMessage();
    }

    public function save(PrivateMessageInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
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
        int $limit
    ): iterable {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT pm FROM %s pm JOIN %s pc ON pc.id = pm.cat_id WHERE (
                    (pm.send_user = :sendUserId AND pm.recip_user = :recipUserId) OR
                    (pm.send_user = :recipUserId AND pm.recip_user = :sendUserId)) AND
                (pc.special = %d OR pc.special = %d) ORDER BY pm.date DESC',
                PrivateMessage::class,
                PrivateMessageFolder::class,
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_MAIN,
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_PMOUT
            )
        )->setParameters([
            'sendUserId' => $senderUserId,
            'recipUserId' => $recipientUserId
        ])->setMaxResults($limit)
            ->getResult();
    }

    public function getBySender(int $userId): iterable
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
    ): iterable {
        return $this->findBy(
            ['recip_user' => $userId, 'cat_id' => $folderId],
            ['date' => 'desc', 'id' => 'desc'],
            $limit,
            $offset
        );
    }

    public function getAmountByFolder(int $folderId): int
    {
        return $this->count([
            'cat_id' => $folderId
        ]);
    }

    public function getNewAmountByFolder(int $folderId): int
    {
        return $this->count([
            'cat_id' => $folderId,
            'new' => 1
        ]);
    }

    public function truncateByFolder(int $folderId): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s pm WHERE pm.cat_id = :folderId',
                PrivateMessage::class
            )
        )->setParameters([
            'folderId' => $folderId
        ])->execute();
    }
}