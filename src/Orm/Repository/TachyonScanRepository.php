<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
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

    public function findActiveByShipLocationAndOwner(ShipInterface $ship): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT ts FROM %s ts
                WHERE ts.scan_time > :theTime
                AND ts.user_id = :userId
                AND (CASE WHEN :isSystem = true
                                THEN EXISTS (SELECT ss.id
                                                FROM %s ss
                                                WHERE ss.id = ts.starsystem_map_id
                                                AND ss.sx = :sx and ss.sy = :sy and ss.systems_id = :systemId)
                                ELSE EXISTS (SELECT m.id
                                                FROM %s m
                                                WHERE m.id = ts.map_id
                                                AND m.cx = :cx and m.cy = :cy)
                            END)',
                TachyonScan::class,
                StarSystemMap::class,
                Map::class
            )
        )->setParameters([
            'isSystem' => $ship->getSystem !== null,
            'systemId' => $ship->getSystem()->getId(),
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
