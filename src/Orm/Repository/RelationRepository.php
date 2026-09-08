<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\Relation;
use Stu\Orm\Entity\RelationPermission;
use Stu\Orm\Entity\User;

/**
 * @extends EntityRepository<Relation>
 */
final class RelationRepository extends EntityRepository implements RelationRepositoryInterface
{
    public function prototype(): Relation
    {
        return new Relation();
    }

    public function save(Relation $relation): void
    {
        $relation->validateParties();
        $this->getEntityManager()->persist($relation);
    }

    public function delete(Relation $relation): void
    {
        $this->deletePermissionsByRelations([$relation]);
        $this->getEntityManager()->remove($relation);
    }

    public function truncateByUser(User $user): void
    {
        $this->deleteBy('r.sourceUser = :user OR r.recipientUser = :user', ['user' => $user]);
    }

    public function truncateByAlliance(Alliance $alliance): void
    {
        $this->deleteBy('r.sourceAlliance = :alliance OR r.recipientAlliance = :alliance', [
            'alliance' => $alliance
        ]);
    }

    public function truncateByAlliances(Alliance $alliance, Alliance $opponent): void
    {
        $this->deleteBy(
            'r.sourceAlliance IN (:alliance, :opponent) AND r.recipientAlliance IN (:alliance, :opponent)',
            ['alliance' => $alliance, 'opponent' => $opponent]
        );
    }

    /** @return array<int, Relation> */
    public function getActive(): array
    {
        return $this->query(
            'r.date > 0 AND r.sourceAlliance IS NOT NULL AND r.recipientAlliance IS NOT NULL ORDER BY r.id ASC'
        )->getResult();
    }

    /**
     * @param array<int, int> $typeIds
     * @return array<int, Relation>
     */
    public function getActiveByTypes(array $typeIds): array
    {
        return $this->query(
            'r.date > 0 AND r.type IN (:typeIds) ORDER BY r.id ASC',
            ['typeIds' => $typeIds]
        )->getResult();
    }

    /** @return array<int, Relation> */
    public function getByAlliance(int $allianceId): array
    {
        return $this->query(
            'r.sourceAlliance IS NOT NULL AND r.recipientAlliance IS NOT NULL AND (r.sourceAlliance = :allianceId OR r.recipientAlliance = :allianceId)',
            ['allianceId' => $allianceId]
        )->getResult();
    }

    /** @return array<int, Relation> */
    public function getByAlliancePair(int $allianceId, int $opponentId): array
    {
        return $this->query(
            'r.sourceAlliance IN (:allianceId, :opponentId) AND r.recipientAlliance IN (:allianceId, :opponentId)',
            ['allianceId' => $allianceId, 'opponentId' => $opponentId]
        )->getResult();
    }

    public function getPendingCountByAlliances(int $allianceId, int $opponentId): int
    {
        return (int) $this
            ->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT COUNT(r.id) FROM %s r WHERE r.date = 0 AND r.sourceAlliance IN (:allianceId, :opponentId) AND r.recipientAlliance IN (:allianceId, :opponentId)',
                    Relation::class
                )
            )
            ->setParameters(['allianceId' => $allianceId, 'opponentId' => $opponentId])
            ->getSingleScalarResult();
    }

    /** @return array<int, Relation> */
    public function getActiveByAlliance(int $allianceId): array
    {
        return $this->query(
            'r.date > 0 AND r.sourceAlliance IS NOT NULL AND r.recipientAlliance IS NOT NULL AND (r.sourceAlliance = :allianceId OR r.recipientAlliance = :allianceId) ORDER BY r.id ASC',
            ['allianceId' => $allianceId]
        )->getResult();
    }

    public function getActiveByAlliancePair(int $allianceId, int $opponentId): ?Relation
    {
        return $this->getActiveByTypeAndAlliancePair([], $allianceId, $opponentId);
    }

    /** @param array<int, int> $typeIds */
    public function getActiveByParties(
        array $typeIds,
        User|Alliance $firstParty,
        User|Alliance $secondParty
    ): ?Relation {
        $firstSourceField = $this->getPartyField($firstParty, 'source');
        $firstRecipientField = $this->getPartyField($firstParty, 'recipient');
        $secondSourceField = $this->getPartyField($secondParty, 'source');
        $secondRecipientField = $this->getPartyField($secondParty, 'recipient');

        return $this->getActiveRelation(
            $typeIds,
            "(r.$firstSourceField = :firstParty AND r.$secondRecipientField = :secondParty)"
            . " OR (r.$secondSourceField = :secondParty AND r.$firstRecipientField = :firstParty)",
            ['firstParty' => $firstParty, 'secondParty' => $secondParty]
        );
    }

    /** @param array<int, int> $typeIds */
    public function getActiveByTypeAndAlliancePair(
        array $typeIds,
        int $allianceId,
        int $opponentId
    ): ?Relation {
        $where = 'r.date > 0 AND r.sourceAlliance IN (:allianceId, :opponentId) AND r.recipientAlliance IN (:allianceId, :opponentId)';
        $parameters = ['allianceId' => $allianceId, 'opponentId' => $opponentId];
        if ($typeIds !== []) {
            $where .= ' AND r.type IN (:typeIds)';
            $parameters['typeIds'] = $typeIds;
        }

        return $this->query($where, $parameters)->getOneOrNullResult();
    }

    /** @return array<int, Relation> */
    public function getByUserAndAlliance(User $user, ?Alliance $alliance): array
    {
        $where = 'r.sourceUser = :user OR r.recipientUser = :user';
        $parameters = ['user' => $user];
        if ($alliance !== null) {
            $where .= ' OR r.sourceAlliance = :alliance OR r.recipientAlliance = :alliance';
            $parameters['alliance'] = $alliance;
        }

        return $this->query(
            "($where) AND (r.sourceUser IS NOT NULL OR r.recipientUser IS NOT NULL) ORDER BY r.id ASC",
            $parameters
        )->getResult();
    }

    /** @return array<int, Relation> */
    public function getByUserPair(User $firstUser, User $secondUser): array
    {
        return $this->query(
            'r.sourceAlliance IS NULL AND r.recipientAlliance IS NULL AND ((r.sourceUser = :firstUser AND r.recipientUser = :secondUser) OR (r.sourceUser = :secondUser AND r.recipientUser = :firstUser))',
            ['firstUser' => $firstUser, 'secondUser' => $secondUser]
        )->getResult();
    }

    /** @return array<int, Relation> */
    public function getByAllianceAndUserPair(Alliance $alliance, User $user): array
    {
        return $this->query(
            '((r.sourceAlliance = :alliance AND r.recipientUser = :user AND r.sourceUser IS NULL AND r.recipientAlliance IS NULL) OR (r.recipientAlliance = :alliance AND r.sourceUser = :user AND r.recipientUser IS NULL AND r.sourceAlliance IS NULL))',
            ['alliance' => $alliance, 'user' => $user]
        )->getResult();
    }

    /** @param array<int, int> $typeIds */
    public function getActiveByUserPair(array $typeIds, User $firstUser, User $secondUser): ?Relation
    {
        return $this->getActiveRelation(
            $typeIds,
            'r.sourceAlliance IS NULL AND r.recipientAlliance IS NULL AND ((r.sourceUser = :firstUser AND r.recipientUser = :secondUser) OR (r.sourceUser = :secondUser AND r.recipientUser = :firstUser))',
            ['firstUser' => $firstUser, 'secondUser' => $secondUser]
        );
    }

    /** @param array<int, int> $typeIds */
    public function getActiveByAllianceAndUserPair(array $typeIds, Alliance $alliance, User $user): ?Relation
    {
        return $this->getActiveRelation(
            $typeIds,
            '((r.sourceAlliance = :alliance AND r.recipientUser = :user AND r.sourceUser IS NULL AND r.recipientAlliance IS NULL) OR (r.recipientAlliance = :alliance AND r.sourceUser = :user AND r.recipientUser IS NULL AND r.sourceAlliance IS NULL))',
            ['alliance' => $alliance, 'user' => $user]
        );
    }

    private function getPartyField(User|Alliance $party, string $position): string
    {
        if ($party instanceof User) {
            return $position === 'source' ? 'sourceUser' : 'recipientUser';
        }

        return $position === 'source' ? 'sourceAlliance' : 'recipientAlliance';
    }

    /**
     * @param array<int, int> $typeIds
     * @param array<string, mixed> $parameters
     */
    private function getActiveRelation(array $typeIds, string $where, array $parameters): ?Relation
    {
        if ($typeIds !== []) {
            $where = "r.type IN (:typeIds) AND ($where)";
            $parameters['typeIds'] = $typeIds;
        }

        return $this->query("r.date > 0 AND ($where)", $parameters)->getOneOrNullResult();
    }

    /**
     * @param array<string, mixed> $parameters
     * @return \Doctrine\ORM\Query<int, Relation>
     */
    private function query(string $where, array $parameters = []): \Doctrine\ORM\Query
    {
        return $this
            ->getEntityManager()
            ->createQuery(
                sprintf('SELECT r FROM %s r WHERE %s', Relation::class, $where)
            )
            ->setParameters($parameters);
    }

    /** @param array<string, mixed> $parameters */
    private function deleteBy(string $where, array $parameters): void
    {
        $relations = $this->query($where, $parameters)->getResult();
        $this->deletePermissionsByRelations($relations);

        $this
            ->getEntityManager()
            ->createQuery(
                sprintf('DELETE FROM %s r WHERE %s', Relation::class, $where)
            )
            ->setParameters($parameters)
            ->execute();
    }

    /** @param array<int, Relation> $relations */
    private function deletePermissionsByRelations(array $relations): void
    {
        if ($relations === []) {
            return;
        }

        $this
            ->getEntityManager()
            ->createQuery(
                sprintf('DELETE FROM %s rp WHERE rp.relation IN (:relations)', RelationPermission::class)
            )
            ->setParameter('relations', $relations)
            ->execute();
    }
}
