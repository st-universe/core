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
use Stu\Orm\Entity\SpacecraftInterface;
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
    public function getAmountBySpacecraft(SpacecraftInterface $spacecraft): int
    {
        return $this->count([
            'spacecraft' => $spacecraft
        ]);
    }

    #[Override]
    public function hasEnoughCrew(SpacecraftInterface $spacecraft): bool
    {
        return $this->getAmountBySpacecraft($spacecraft) >= $spacecraft->getNeededCrewCount();
    }

    #[Override]
    public function hasCrewmanOfUser(SpacecraftInterface $spacecraft, int $userId): bool
    {
        return (int)$this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT count(ca.crew)
                    FROM %s ca
                    JOIN %s c
                    WITH ca.crew = c
                    WHERE c.user_id = :userId
                    AND ca.spacecraft = :spacecraft',
                    CrewAssignment::class,
                    Crew::class
                )
            )
            ->setParameters([
                'userId' => $userId,
                'spacecraft' => $spacecraft
            ])
            ->getSingleScalarResult() > 0;
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
    public function getByUserAtColonies(UserInterface $user): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT ca
                    FROM %s ca
                    WHERE ca.user = :user
                    AND ca.colony IS NOT NULL',
                    CrewAssignment::class
                )
            )
            ->setParameter('user', $user)
            ->getResult();
    }

    #[Override]
    public function getByUserOnEscapePods(UserInterface $user): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT ca
                    FROM %s ca
                    JOIN %s s
                    WITH ca.spacecraft = s
                    JOIN %s r
                    WITH s.rump_id = r.id
                    WHERE ca.user = :user
                    AND r.category_id = :categoryId',
                    CrewAssignment::class,
                    Spacecraft::class,
                    SpacecraftRump::class
                )
            )
            ->setParameters([
                'user' => $user,
                'categoryId' => SpacecraftRumpEnum::SHIP_CATEGORY_ESCAPE_PODS
            ])
            ->getResult();
    }

    #[Override]
    public function getByUserAtTradeposts(UserInterface $user): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT ca
                    FROM %s ca
                    WHERE ca.user = :user
                    AND ca.tradepost IS NOT NULL',
                    CrewAssignment::class
                )
            )
            ->setParameter('user', $user)
            ->getResult();
    }

    #[Override]
    public function getAmountByUserOnColonies(UserInterface $user): int
    {
        return (int)$this->getEntityManager()->createQuery(
            sprintf(
                'SELECT count(ca.crew)
                FROM %s ca
                WHERE ca.user = :user
                AND ca.colony IS NOT NULL',
                CrewAssignment::class
            )
        )->setParameter('user', $user)->getSingleScalarResult();
    }

    #[Override]
    public function getAmountByUserOnShips(UserInterface $user): int
    {
        return (int)$this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT count(ca.crew)
                    FROM %s ca
                    WHERE ca.user = :user
                    AND ca.spacecraft IS NOT NULL',
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
                    'SELECT count(ca.crew)
                    FROM %s ca
                    WHERE ca.user = :user
                    AND ca.tradepost IS NOT NULL',
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
    public function truncateBySpacecraft(SpacecraftInterface $spacecraft): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s ca WHERE ca.spacecraft = :spacecraft',
                    CrewAssignment::class
                )
            )
            ->setParameter('spacecraft', $spacecraft)
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
                    'SELECT COUNT(ca.crew)
                        FROM %s ca
                        JOIN %s c WITH ca.crew = c
                        JOIN %s st WITH ca.spacecraft = st
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
