<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Component\Game\TimeConstants;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserProfileVisitor;
use Stu\Orm\Entity\UserProfileVisitorInterface;

/**
 * @extends EntityRepository<UserProfileVisitor>
 */
final class UserProfileVisitorRepository extends EntityRepository implements UserProfileVisitorRepositoryInterface
{
    #[Override]
    public function isVisitRegistered(UserInterface $user, UserInterface $visitor): bool
    {
        return $this->count([
            'user' => $visitor,
            'opponent' => $user,
        ]) !== 0;
    }

    #[Override]
    public function prototype(): UserProfileVisitorInterface
    {
        return new UserProfileVisitor();
    }

    #[Override]
    public function save(UserProfileVisitorInterface $userProfileVisitor): void
    {
        $em = $this->getEntityManager();

        $em->persist($userProfileVisitor);
        $em->flush();
    }

    #[Override]
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
                'date' => time() - TimeConstants::ONE_DAY_IN_SECONDS
            ])
            ->getResult();
    }

    #[Override]
    public function truncateByUser(UserInterface $user): void
    {
        $q = $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s p WHERE p.user_id = :user',
                UserProfileVisitor::class
            )
        );
        $q->setParameter('user', $user);
        $q->execute();
    }

    #[Override]
    public function truncateAllEntries(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s upv',
                UserProfileVisitor::class
            )
        )->execute();
    }
}
