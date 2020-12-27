<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
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
        $rsm->addScalarResult('scan_id', 'scan_id', 'integer');

        return $this->getEntityManager()->createNativeQuery(
                'SELECT ts.id as scan_id FROM stu_tachyon_scan ts
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
            'isSystem' => $ship->getSystem !== null,
            'systemId' => $ship->getSystem !== null ? $ship->getSystem()->getId() : 0,
            'sx' => $sx,
            'sy' => $sy,
            'cx' => $cx,
            'cy' => $cy,
            'theTime' => time() - 300,
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
