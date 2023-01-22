<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Component\Ship\System\Type\TachyonScannerShipSystem;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\TachyonScan;
use Stu\Orm\Entity\TachyonScanInterface;

/**
 * @extends EntityRepository<TachyonScan>
 */
final class TachyonScanRepository extends EntityRepository implements TachyonScanRepositoryInterface
{
    public function prototype(): TachyonScanInterface
    {
        return new TachyonScan();
    }

    public function findActiveByShipLocationAndOwner(ShipInterface $ship): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(TachyonScan::class, 't');
        $rsm->addFieldResult('t', 'id', 'id');
        $rsm->addFieldResult('t', 'user_id', 'user_id');
        $rsm->addFieldResult('t', 'map_id', 'map_id');
        $rsm->addFieldResult('t', 'starsystem_map_id', 'starsystem_map_id');
        $rsm->addFieldResult('t', 'scan_time', 'scan_time');

        $isSystem = $ship->getSystem() !== null;

        return $this->getEntityManager()->createNativeQuery(
            sprintf(
                'SELECT ts.id as id, ts.user_id as user_id, ts.map_id as map_id,
                    ts.starsystem_map_id as starsystem_map_id, ts.scan_time as scan_time
                FROM stu_tachyon_scan ts
                JOIN %s mf
                ON ts.%s = mf.id
                AND mf.id = :mapId
                WHERE ts.scan_time > :theTime
                AND ts.user_id = :userId',
                $isSystem ? 'stu_sys_map' : 'stu_map',
                $isSystem ? 'starsystem_map_id' : 'map_id'
            ),
            $rsm
        )->setParameters([
            'mapId' => $isSystem ? $ship->getStarsystemMap()->getId() : $ship->getMap()->getId(),
            'theTime' => time() - TachyonScannerShipSystem::DECLOAK_INTERVAL,
            'userId' => $ship->getUser()->getId()
        ])->getResult();
    }

    public function save(TachyonScanInterface $obj): void
    {
        $em = $this->getEntityManager();

        $em->persist($obj);
    }

    public function deleteOldScans(int $threshold): void
    {
        $q = $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s ts WHERE ts.scan_time < :maxAge',
                TachyonScan::class
            )
        );
        $q->setParameter('maxAge', time() - $threshold);
        $q->execute();
    }
}
