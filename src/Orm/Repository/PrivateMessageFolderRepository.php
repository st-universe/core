<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\PrivateMessageFolder;
use Stu\Orm\Entity\PrivateMessageFolderInterface;

final class PrivateMessageFolderRepository extends EntityRepository implements PrivateMessageFolderRepositoryInterface
{

    public function prototype(): PrivateMessageFolderInterface
    {
        return new PrivateMessageFolder();
    }

    public function save(PrivateMessageFolderInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    public function delete(PrivateMessageFolderInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush();
    }

    public function getOrderedByUser(int $userId): iterable
    {
        return $this->findBy(
            ['user_id' => $userId],
            ['sort' => 'asc'],
        );
    }

    public function getByUserAndSpecial(int $userId, int $specialId): ?PrivateMessageFolderInterface
    {
        return $this->findOneBy([
            'user_id' => $userId,
            'special' => $specialId
        ]);
    }

    public function getMaxOrderIdByUser(int $userId): int
    {
        return (int)$this->getEntityManager()->createQuery(
            sprintf(
                'SELECT MAX(pmf.sort) FROM %s pmf WHERE pmf.user_id = :userId',
                PrivateMessageFolder::class
            )
        )->setParameters([
            'userId' => $userId
        ])->getSingleScalarResult();
    }
}
