<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

use Stu\Component\Ship\System\Type\TachyonScannerShipSystem;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\TachyonScan;
use Stu\Orm\Entity\TachyonScanInterface;

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

        return $this->getEntityManager()->createNativeQuery(
                'SELECT ts.id as id, ts.user_id as user_id, ts.map_id as map_id, ts.starsystem_map_id as starsystem_map_id,
                        ts.scan_time as scan_time
                FROM stu_tachyon_scan ts
                WHERE ts.scan_time > :theTime
                AND ts.user_id = :userId
                AND (CASE WHEN :isSystem = true
                                THEN EXISTS (SELECT ss.id
                                                FROM stu_sys_map ss
                                                WHERE ss.id = ts.starsystem_map_id
                                                AND ss.sx = :sx and ss.sy = :sy and ss.systems_id = :systemId)
                                ELSE EXISTS (SELECT m.id
                                                FROM stu_map m
                                                WHERE m.id = ts.map_id
                                                AND m.cx = :cx and m.cy = :cy)
                            END)', $rsm
        )->setParameters([
            'isSystem' => $ship->getSystem() !== null,
            'systemId' => $ship->getSystem() !== null ? $ship->getSystem()->getId() : 0,
            'sx' => $ship->getSx(),
            'sy' => $ship->getSy(),
            'cx' => $ship->getCx(),
            'cy' => $ship->getCy(),
            'theTime' => time() - TachyonScannerShipSystem::DECLOAK_INTERVAL,
            'userId' => $ship->getUser()->getId()
        ])->getResult();
    }

    public function save(TachyonScanInterface $obj): void
    {
        $em = $this->getEntityManager();

        $em->persist($obj);
        $em->flush();
    }
}
