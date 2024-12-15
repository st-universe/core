<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Override;
use Stu\Orm\Entity\StationShipRepair;
use Stu\Orm\Entity\StationShipRepairInterface;

/**
 * @extends EntityRepository<StationShipRepair>
 */
final class StationShipRepairRepository extends EntityRepository implements StationShipRepairRepositoryInterface
{
    #[Override]
    public function prototype(): StationShipRepairInterface
    {
        return new StationShipRepair();
    }

    #[Override]
    public function getByStation(int $stationId): array
    {
        return $this->findBy([
            'station_id' => $stationId
        ], ['id' => 'asc']);
    }

    #[Override]
    public function getByShip(int $shipId): ?StationShipRepairInterface
    {
        return $this->findOneBy([
            'ship_id' => $shipId
        ]);
    }

    #[Override]
    public function getMostRecentJobs(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(StationShipRepair::class, 's');
        $rsm->addFieldResult('s', 'id', 'id');
        $rsm->addFieldResult('s', 'station_id', 'station_id');
        $rsm->addFieldResult('s', 'ship_id', 'ship_id');

        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT s.id, s.station_id, s.ship_id
                FROM    (
                        SELECT *, ROW_NUMBER() OVER (PARTITION BY station_id ORDER BY id ASC) rn
                        FROM stu_station_shiprepair
                        ) s
                WHERE   s.rn = 1',
                $rsm
            )
            ->getResult();
    }

    #[Override]
    public function save(StationShipRepairInterface $stationShipRepair): void
    {
        $em = $this->getEntityManager();

        $em->persist($stationShipRepair);
    }

    #[Override]
    public function delete(StationShipRepairInterface $stationShipRepair): void
    {
        $em = $this->getEntityManager();

        $em->remove($stationShipRepair);
    }

    #[Override]
    public function truncateByShipId(int $shipId): void
    {
        $q = $this->getEntityManager()->createQuery(
            sprintf(
                'delete from %s t where t.ship_id = :shipId',
                StationShipRepair::class
            )
        );
        $q->setParameter('shipId', $shipId);
        $q->execute();
    }
}
