<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\PrivateMessageFolder;
use Stu\Orm\Entity\PrivateMessageFolderInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends EntityRepository<PrivateMessageFolder>
 */
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
        $em->flush();
    }

    public function delete(PrivateMessageFolderInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush();
    }

    public function getOrderedByUser(int $userId): array
    {
        return $this->findBy(
            ['user_id' => $userId, 'deleted' => NULL],
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

    public function getMaxOrderIdByUser(UserInterface $user): int
    {
        return (int)$this->getEntityManager()->createQuery(
            sprintf(
                'SELECT MAX(pmf.sort) FROM %s pmf WHERE pmf.user = :user',
                PrivateMessageFolder::class
            )
        )->setParameters([
            'user' => $user
        ])->getSingleScalarResult();
    }
}
