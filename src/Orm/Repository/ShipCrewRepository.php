<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Orm\Entity\ShipCrew;
use Stu\Orm\Entity\ShipCrewInterface;

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

    public function getByUserAtTradeposts(int $userId): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id', 'integer');
        $rsm->addScalarResult('name', 'name', 'string');
        $rsm->addScalarResult('sector', 'sector', 'string');
        $rsm->addScalarResult('amount', 'amount', 'integer');

        return $this->getEntityManager()->createNativeQuery(
            'SELECT tp.id as id, tp.name as name, concat(m.cx, \'|\', m.cy) as sector, count(*) as amount
            FROM stu_crew_assign ca
            JOIN stu_trade_posts tp
            ON ca.tradepost_id = tp.id
            JOIN stu_map m
            ON tp.map_id = m.id
            WHERE ca.user_id = :userId
            GROUP BY tp.id, tp.name, m.cx, m.cy',
            $rsm
        )->setParameter('userId', $userId)
            ->getResult();
    }

    public function getAmountByShip(int $shipId): int
    {
        return $this->count([
            'ship_id' => $shipId
        ]);
    }

    public function getAmountByUser(int $userId): int
    {
        return $this->count([
            'user_id' => $userId
        ]);
    }

    public function getAmountByUserOnShips(int $userId): int
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT count(ca.id)
                FROM %s ca
                WHERE ca.user_id = :userId
                AND ca.ship_id IS NOT NULL',
                ShipCrew::class
            )
        )->setParameter('userId', $userId)->getSingleScalarResult();
    }

    public function getAmountByUserAtTradeposts(int $userId): int
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT count(ca.id)
                FROM %s ca
                WHERE ca.user_id = :userId
                AND ca.tradepost_id IS NOT NULL',
                ShipCrew::class
            )
        )->setParameter('userId', $userId)->getSingleScalarResult();
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
            WHERE sc.user_id > 100
            AND sc.user_id = s.user_id
            GROUP BY sc.user_id
            ORDER BY 2 DESC
            LIMIT 10',
            $rsm
        )
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
}
