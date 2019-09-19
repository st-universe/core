<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\PrivateMessage;
use Stu\Orm\Entity\PrivateMessageInterface;

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
        $em->flush($post);
    }

    public function delete(PrivateMessageInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush($post);
    }

    public function getOrderedCorrepondence(
        array $userIdPair,
        array $folderIds,
        int $limit
    ): iterable {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT pm FROM %s pm WHERE (pm.send_user IN (:sendUserIds) OR pm.recip_user IN (:recipUserIds)) AND
                pm.cat_id IN (:folderIds) ORDER BY pm.date DESC'
            )
        )->setParameters([
            'sendUserIds' => $userIdPair,
            'recipUserIds' => $userIdPair,
            'folderIds' => $folderIds
        ])->setMaxResults($limit)
            ->getResult();
    }

    public function getByUserAndFolder(
        int $userId,
        int $folderId,
        int $offset,
        int $limit
    ): iterable {
        return $this->findBy(
            ['recip_user' => $userId, 'cat_id' => $folderId],
            ['date' => 'desc'],
            $limit,
            $offset
        );
    }
}