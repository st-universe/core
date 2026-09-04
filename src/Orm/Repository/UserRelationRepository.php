<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserRelation;

/**
 * @extends EntityRepository<UserRelation>
 */
final class UserRelationRepository extends EntityRepository implements UserRelationRepositoryInterface
{
    #[\Override]
    public function prototype(): UserRelation
    {
        return new UserRelation();
    }

    #[\Override]
    public function save(UserRelation $relation): void
    {
        $this->getEntityManager()->persist($relation);
    }

    #[\Override]
    public function delete(UserRelation $relation): void
    {
        $this->getEntityManager()->remove($relation);
    }

    #[\Override]
    public function truncateByUser(User $user): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s r WHERE r.sourceUser = :user OR r.recipientUser = :user',
                UserRelation::class
            )
        )->setParameter('user', $user)->execute();
    }

    #[\Override]
    public function truncateByAlliance(Alliance $alliance): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s r WHERE r.sourceAlliance = :alliance OR r.recipientAlliance = :alliance',
                UserRelation::class
            )
        )->setParameter('alliance', $alliance)->execute();
    }

    #[\Override]
    public function getByUserAndAlliance(User $user, ?Alliance $alliance): array
    {
        $where = 'r.sourceUser = :user OR r.recipientUser = :user';
        $parameters = ['user' => $user];

        if ($alliance !== null) {
            $where .= ' OR r.sourceAlliance = :alliance OR r.recipientAlliance = :alliance';
            $parameters['alliance'] = $alliance;
        }

        return $this->getEntityManager()->createQuery(
            sprintf('SELECT r FROM %s r WHERE %s ORDER BY r.id ASC', UserRelation::class, $where)
        )->setParameters($parameters)->getResult();
    }

    #[\Override]
    public function getByUserPair(User $firstUser, User $secondUser): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT r FROM %s r
                WHERE r.sourceAlliance IS NULL
                AND r.recipientAlliance IS NULL
                AND ((r.sourceUser = :firstUser AND r.recipientUser = :secondUser)
                    OR (r.sourceUser = :secondUser AND r.recipientUser = :firstUser))',
                UserRelation::class
            )
        )->setParameters([
            'firstUser' => $firstUser,
            'secondUser' => $secondUser,
        ])->getResult();
    }

    #[\Override]
    public function getByAllianceAndUserPair(Alliance $alliance, User $user): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT r FROM %s r
                WHERE ((r.sourceAlliance = :alliance AND r.recipientUser = :user
                    AND r.sourceUser IS NULL AND r.recipientAlliance IS NULL)
                OR (r.recipientAlliance = :alliance AND r.sourceUser = :user
                    AND r.recipientUser IS NULL AND r.sourceAlliance IS NULL))',
                UserRelation::class
            )
        )->setParameters([
            'alliance' => $alliance,
            'user' => $user,
        ])->getResult();
    }

    #[\Override]
    public function getActiveByUserPair(array $typeIds, User $firstUser, User $secondUser): ?UserRelation
    {
        return $this->getActiveRelation(
            $typeIds,
            'r.sourceAlliance IS NULL
            AND r.recipientAlliance IS NULL
            AND ((r.sourceUser = :firstUser AND r.recipientUser = :secondUser)
                OR (r.sourceUser = :secondUser AND r.recipientUser = :firstUser))',
            [
                'firstUser' => $firstUser,
                'secondUser' => $secondUser,
            ]
        );
    }

    #[\Override]
    public function getActiveByAllianceAndUserPair(array $typeIds, Alliance $alliance, User $user): ?UserRelation
    {
        return $this->getActiveRelation(
            $typeIds,
            '((r.sourceAlliance = :alliance AND r.recipientUser = :user
                AND r.sourceUser IS NULL AND r.recipientAlliance IS NULL)
            OR (r.recipientAlliance = :alliance AND r.sourceUser = :user
                AND r.recipientUser IS NULL AND r.sourceAlliance IS NULL))',
            [
                'alliance' => $alliance,
                'user' => $user
            ]
        );
    }

    /**
     * @param array<int> $typeIds
     * @param array<string, Alliance|User> $parameters
     */
    private function getActiveRelation(array $typeIds, string $where, array $parameters): ?UserRelation
    {
        return $this->getEntityManager()->createQuery(
            sprintf('SELECT r FROM %s r WHERE r.date > 0 AND r.type IN (:typeIds) AND (%s)', UserRelation::class, $where)
        )->setParameters([
            ...$parameters,
            'typeIds' => $typeIds,
        ])->getOneOrNullResult();
    }
}
