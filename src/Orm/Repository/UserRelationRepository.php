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
                'DELETE FROM %s r WHERE r.source_user_id = :userId OR r.recipient_user_id = :userId',
                UserRelation::class
            )
        )->setParameter('userId', $user->getId())->execute();
    }

    #[\Override]
    public function truncateByAlliance(Alliance $alliance): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s r WHERE r.source_alliance_id = :allianceId OR r.recipient_alliance_id = :allianceId',
                UserRelation::class
            )
        )->setParameter('allianceId', $alliance->getId())->execute();
    }

    #[\Override]
    public function getByUserAndAlliance(User $user, ?Alliance $alliance): array
    {
        $where = 'r.source_user_id = :userId OR r.recipient_user_id = :userId';
        $parameters = ['userId' => $user->getId()];

        if ($alliance !== null) {
            $where .= ' OR r.source_alliance_id = :allianceId OR r.recipient_alliance_id = :allianceId';
            $parameters['allianceId'] = $alliance->getId();
        }

        return $this->getEntityManager()->createQuery(
            sprintf('SELECT r FROM %s r WHERE %s ORDER BY r.id ASC', UserRelation::class, $where)
        )->setParameters($parameters)->getResult();
    }

    #[\Override]
    public function getByUserPair(int $firstUserId, int $secondUserId): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT r FROM %s r
                WHERE r.source_alliance_id IS NULL
                AND r.recipient_alliance_id IS NULL
                AND ((r.source_user_id = :firstUserId AND r.recipient_user_id = :secondUserId)
                    OR (r.source_user_id = :secondUserId AND r.recipient_user_id = :firstUserId))',
                UserRelation::class
            )
        )->setParameters([
            'firstUserId' => $firstUserId,
            'secondUserId' => $secondUserId,
        ])->getResult();
    }

    #[\Override]
    public function getByAllianceAndUserPair(int $allianceId, int $userId): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT r FROM %s r
                WHERE ((r.source_alliance_id = :allianceId AND r.recipient_user_id = :userId
                    AND r.source_user_id IS NULL AND r.recipient_alliance_id IS NULL)
                OR (r.recipient_alliance_id = :allianceId AND r.source_user_id = :userId
                    AND r.recipient_user_id IS NULL AND r.source_alliance_id IS NULL))',
                UserRelation::class
            )
        )->setParameters([
            'allianceId' => $allianceId,
            'userId' => $userId,
        ])->getResult();
    }

    #[\Override]
    public function getActiveByUserPair(array $typeIds, int $firstUserId, int $secondUserId): ?UserRelation
    {
        return $this->getActiveRelation(
            $typeIds,
            'r.source_alliance_id IS NULL
            AND r.recipient_alliance_id IS NULL
            AND ((r.source_user_id = :firstUserId AND r.recipient_user_id = :secondUserId)
                OR (r.source_user_id = :secondUserId AND r.recipient_user_id = :firstUserId))',
            [
                'firstUserId' => $firstUserId,
                'secondUserId' => $secondUserId,
            ]
        );
    }

    #[\Override]
    public function getActiveByAllianceAndUserPair(array $typeIds, int $allianceId, int $userId): ?UserRelation
    {
        return $this->getActiveRelation(
            $typeIds,
            '((r.source_alliance_id = :allianceId AND r.recipient_user_id = :userId
                AND r.source_user_id IS NULL AND r.recipient_alliance_id IS NULL)
            OR (r.recipient_alliance_id = :allianceId AND r.source_user_id = :userId
                AND r.recipient_user_id IS NULL AND r.source_alliance_id IS NULL))',
            [
                'allianceId' => $allianceId,
                'userId' => $userId,
            ]
        );
    }

    /**
     * @param array<int> $typeIds
     * @param array<string, int> $parameters
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
