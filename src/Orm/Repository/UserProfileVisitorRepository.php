<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\UserProfileVisitor;
use Stu\Orm\Entity\UserProfileVisitorInterface;

final class UserProfileVisitorRepository extends EntityRepository implements UserProfileVisitorRepositoryInterface
{
    public function isVisitRegistered(int $profileUserId, int $userId): bool
    {
        return $this->count([
                'user_id' => $userId,
                'recipient' => $profileUserId,
            ]) > 0;
    }

    public function prototype(): UserProfileVisitorInterface
    {
        return new UserProfileVisitor();
    }

    public function save(UserProfileVisitorInterface $userProfileVisitor): void
    {
        $em = $this->getEntityManager();

        $em->persist($userProfileVisitor);
        $em->flush($userProfileVisitor);
    }

    public function getRecent(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT p FROM %s p WHERE p.recipient = :userId AND p.date > :date ORDER BY p.date DESC',
                    UserProfileVisitor::class
                )
            )
            ->setParameters([
                'userId' => $userId,
                'date' => time() - 86400
            ])
            ->getResult();
    }

    public function truncateByUser(int $userId): void
    {
        $q = $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s p WHERE p.user_id = :userId',
                UserProfileVisitor::class
            )
        );
        $q->setParameter('userId', $userId);
        $q->execute();
    }
}