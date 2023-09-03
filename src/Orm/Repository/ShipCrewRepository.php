<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\ShipCrew;
use Stu\Orm\Entity\ShipCrewInterface;
use Stu\Orm\Entity\ShipRump;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends EntityRepository<ShipCrew>
 */
final class ShipCrewRepository extends EntityRepository implements ShipCrewRepositoryInterface
{
    public function prototype(): ShipCrewInterface
    {
        return new ShipCrew();
    }

    public function save(ShipCrewInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    public function delete(ShipCrewInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
    }

    public function getByShip(int $shipId): array
    {
        return $this->findBy(
            ['ship_id' => $shipId],
            ['slot' => 'asc']
        );
    }

    public function getByShipAndSlot(int $shipId, int $slotId): array
    {
        return $this->findBy([
            'ship_id' => $shipId,
            'slot' => $slotId
        ]);
    }

    /**
     * @return array<array{id: int, name: string, sector: string, amount: int}>
     */
    public function getOrphanedSummaryByUserAtTradeposts(int $userId): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id', 'integer');
        $rsm->addScalarResult('name', 'name');
        $rsm->addScalarResult('sector', 'sector');
        $rsm->addScalarResult('amount', 'amount', 'integer');

        return $this->getEntityManager()->createNativeQuery(
            'SELECT tp.id as id, tp.name as name, concat(m.cx, \'|\', m.cy) as sector, count(*) as amount
            FROM stu_crew_assign ca
            JOIN stu_trade_posts tp
            ON ca.tradepost_id = tp.id
            JOIN stu_ships s
            ON tp.ship_id = s.id
            JOIN stu_map m
            ON s.map_id = m.id
            WHERE ca.user_id = :userId
            GROUP BY tp.id, tp.name, m.cx, m.cy',
            $rsm
        )->setParameter('userId', $userId)
            ->getResult();
    }

    public function getAmountByUser(UserInterface $user): int
    {
        return $this->count([
            'user' => $user
        ]);
    }

    public function getByUserAtColonies(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT ca
                    FROM %s ca
                    WHERE ca.user_id = :userId
                    AND ca.colony_id IS NOT NULL',
                    ShipCrew::class
                )
            )
            ->setParameter('userId', $userId)
            ->getResult();
    }

    public function getByUserOnEscapePods(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT ca
                    FROM %s ca
                    JOIN %s s
                    WITH ca.ship_id = s.id
                    JOIN %s r
                    WITH s.rumps_id = r.id
                    WHERE ca.user_id = :userId
                    AND r.category_id = :categoryId',
                    ShipCrew::class,
                    Ship::class,
                    ShipRump::class
                )
            )
            ->setParameters([
                'userId' => $userId,
                'categoryId' => ShipRumpEnum::SHIP_CATEGORY_ESCAPE_PODS
            ])
            ->getResult();
    }

    public function getByUserAtTradeposts(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT ca
                    FROM %s ca
                    WHERE ca.user_id = :userId
                    AND ca.tradepost_id IS NOT NULL',
                    ShipCrew::class
                )
            )
            ->setParameter('userId', $userId)
            ->getResult();
    }

    public function getAmountByUserOnColonies(int $userId): int
    {
        return (int)$this->getEntityManager()->createQuery(
            sprintf(
                'SELECT count(ca.id)
                FROM %s ca
                WHERE ca.user_id = :userId
                AND ca.colony_id IS NOT NULL',
                ShipCrew::class
            )
        )->setParameter('userId', $userId)->getSingleScalarResult();
    }

    public function getAmountByUserOnShips(UserInterface $user): int
    {
        return (int)$this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT count(ca.id)
                    FROM %s ca
                    WHERE ca.user = :user
                    AND ca.ship_id IS NOT NULL',
                    ShipCrew::class
                )
            )
            ->setParameter('user', $user)
            ->getSingleScalarResult();
    }

    public function getAmountByUserAtTradeposts(UserInterface $user): int
    {
        return (int)$this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT count(ca.id)
                    FROM %s ca
                    WHERE ca.user = :user
                    AND ca.tradepost_id IS NOT NULL',
                    ShipCrew::class
                )
            )
            ->setParameter('user', $user)
            ->getSingleScalarResult();
    }

    public function getCrewsTop10(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('user_id', 'user_id', 'integer');
        $rsm->addScalarResult('race', 'race', 'integer');
        $rsm->addScalarResult('crewc', 'crewc', 'integer');

        return $this->getEntityManager()->createNativeQuery(
            'SELECT sc.user_id, count(*) as crewc,
                (SELECT race
                FROM stu_user u
                WHERE sc.user_id = u.id)
            FROM stu_crew_assign sc
            JOIN stu_ships s
            ON sc.ship_id = s.id
            WHERE sc.user_id > :firstUserId
            AND sc.user_id = s.user_id
            GROUP BY sc.user_id
            ORDER BY 2 DESC
            LIMIT 10',
            $rsm
        )->setParameter('firstUserId', UserEnum::USER_FIRST_ID)
            ->getResult();
    }

    public function truncateByShip(int $shipId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s sc WHERE sc.ship_id = :shipId',
                    ShipCrew::class
                )
            )
            ->setParameter('shipId', $shipId)
            ->execute();
    }

    public function truncateByUser(int $userId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s sc WHERE sc.user_id = :userId',
                    ShipCrew::class
                )
            )
            ->setParameter('userId', $userId)
            ->execute();
    }
}
