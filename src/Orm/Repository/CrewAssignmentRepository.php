<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Override;
use Stu\Component\Spacecraft\SpacecraftRumpEnum;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\CrewAssignmentInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\Station;

/**
 * @extends EntityRepository<CrewAssignment>
 */
final class CrewAssignmentRepository extends EntityRepository implements CrewAssignmentRepositoryInterface
{
    #[Override]
    public function prototype(): CrewAssignmentInterface
    {
        return new CrewAssignment();
    }

    #[Override]
    public function save(CrewAssignmentInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    #[Override]
    public function delete(CrewAssignmentInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
    }

    #[Override]
    public function getByShip(int $shipId): array
    {
        return $this->findBy(
            ['spacecraft_id' => $shipId],
            ['slot' => 'asc']
        );
    }

    #[Override]
    public function getByShipAndSlot(int $shipId, int $slotId): array
    {
        return $this->findBy([
            'spacecraft_id' => $shipId,
            'slot' => $slotId
        ]);
    }

    /**
     * @return array<array{id: int, name: string, sector: string, amount: int}>
     */
    #[Override]
    public function getOrphanedSummaryByUserAtTradeposts(int $userId): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id', 'integer');
        $rsm->addScalarResult('name', 'name');
        $rsm->addScalarResult('sector', 'sector');
        $rsm->addScalarResult('amount', 'amount', 'integer');

        return $this->getEntityManager()->createNativeQuery(
            'SELECT tp.id as id, tp.name as name, concat(l.cx, \'|\', l.cy) as sector, count(*) as amount
            FROM stu_crew_assign ca
            JOIN stu_trade_posts tp
            ON ca.tradepost_id = tp.id
            JOIN stu_station s
            ON tp.station_id = s.id
            JOIN stu_spacecraft sp
            ON s.id = sp.id
            JOIN stu_map m
            ON sp.location_id = m.id
            JOIN stu_location l
            ON m.id = l.id
            WHERE ca.user_id = :userId
            GROUP BY tp.id, tp.name, l.cx, l.cy',
            $rsm
        )->setParameter('userId', $userId)
            ->getResult();
    }

    #[Override]
    public function getAmountByUser(UserInterface $user): int
    {
        return $this->count([
            'user' => $user
        ]);
    }

    #[Override]
    public function getByUserAtColonies(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT ca
                    FROM %s ca
                    WHERE ca.user_id = :userId
                    AND ca.colony_id IS NOT NULL',
                    CrewAssignment::class
                )
            )
            ->setParameter('userId', $userId)
            ->getResult();
    }

    #[Override]
    public function getByUserOnEscapePods(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT ca
                    FROM %s ca
                    JOIN %s s
                    WITH ca.spacecraft_id = s.id
                    JOIN %s r
                    WITH s.rump_id = r.id
                    WHERE ca.user_id = :userId
                    AND r.category_id = :categoryId',
                    CrewAssignment::class,
                    Spacecraft::class,
                    SpacecraftRump::class
                )
            )
            ->setParameters([
                'userId' => $userId,
                'categoryId' => SpacecraftRumpEnum::SHIP_CATEGORY_ESCAPE_PODS
            ])
            ->getResult();
    }

    #[Override]
    public function getByUserAtTradeposts(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT ca
                    FROM %s ca
                    WHERE ca.user_id = :userId
                    AND ca.tradepost_id IS NOT NULL',
                    CrewAssignment::class
                )
            )
            ->setParameter('userId', $userId)
            ->getResult();
    }

    #[Override]
    public function getAmountByUserOnColonies(int $userId): int
    {
        return (int)$this->getEntityManager()->createQuery(
            sprintf(
                'SELECT count(ca.id)
                FROM %s ca
                WHERE ca.user_id = :userId
                AND ca.colony_id IS NOT NULL',
                CrewAssignment::class
            )
        )->setParameter('userId', $userId)->getSingleScalarResult();
    }

    #[Override]
    public function getAmountByUserOnShips(UserInterface $user): int
    {
        return (int)$this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT count(ca.id)
                    FROM %s ca
                    WHERE ca.user = :user
                    AND ca.spacecraft_id IS NOT NULL',
                    CrewAssignment::class
                )
            )
            ->setParameter('user', $user)
            ->getSingleScalarResult();
    }

    #[Override]
    public function getAmountByUserAtTradeposts(UserInterface $user): int
    {
        return (int)$this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT count(ca.id)
                    FROM %s ca
                    WHERE ca.user = :user
                    AND ca.tradepost_id IS NOT NULL',
                    CrewAssignment::class
                )
            )
            ->setParameter('user', $user)
            ->getSingleScalarResult();
    }

    #[Override]
    public function getCrewsTop10(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('user_id', 'user_id', 'integer');
        $rsm->addScalarResult('factionid', 'factionid', 'integer');
        $rsm->addScalarResult('crewc', 'crewc', 'integer');

        return $this->getEntityManager()->createNativeQuery(
            'SELECT ca.user_id, count(*) as crewc,
                (SELECT race as factionid
                FROM stu_user u
                WHERE ca.user_id = u.id) as factionid
            FROM stu_crew_assign ca
            JOIN stu_spacecraft s
            ON ca.spacecraft_id = s.id
            WHERE ca.user_id >= :firstUserId
            GROUP BY ca.user_id
            ORDER BY 2 DESC
            LIMIT 10',
            $rsm
        )->setParameter('firstUserId', UserEnum::USER_FIRST_ID)
            ->getResult();
    }

    #[Override]
    public function truncateByShip(int $shipId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s sc WHERE sc.spacecraft_id = :shipId',
                    CrewAssignment::class
                )
            )
            ->setParameter('shipId', $shipId)
            ->execute();
    }

    #[Override]
    public function truncateByUser(int $userId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s sc WHERE sc.user_id = :userId',
                    CrewAssignment::class
                )
            )
            ->setParameter('userId', $userId)
            ->execute();
    }

    #[Override]
    public function hasCrewOnForeignStation(UserInterface $user): bool
    {
        return (int) $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT COUNT(ca.id)
                 FROM %s ca
                 JOIN %s c WITH ca.crew_id = c.id
                 JOIN %s st WITH ca.spacecraft_id = st.id
                 WHERE c.user_id = :user
                 AND st.user_id != :user',
                    CrewAssignment::class,
                    Crew::class,
                    Station::class
                )
            )
            ->setParameter('user', $user->getId())
            ->getSingleScalarResult() > 0;
    }
}
