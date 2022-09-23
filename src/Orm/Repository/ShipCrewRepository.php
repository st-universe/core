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
            ['ships_id' => $shipId],
            ['slot' => 'asc']
        );
    }

    public function getByShipAndSlot(int $shipId, int $slotId): array
    {
        return $this->findBy([
            'ships_id' => $shipId,
            'slot' => $slotId
        ]);
    }

    public function getAmountByShip(int $shipId): int
    {
        return $this->count([
            'ships_id' => $shipId
        ]);
    }

    public function getAmountByUser(int $userId): int
    {
        return $this->count([
            'user_id' => $userId
        ]);
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
            FROM stu_ships_crew sc
            JOIN stu_ships s
            ON sc.ships_id = s.id
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
                    'DELETE FROM %s sc WHERE sc.ships_id = :shipId',
                    ShipCrew::class
                )
            )
            ->setParameter('shipId', $shipId)
            ->execute();
    }
}
