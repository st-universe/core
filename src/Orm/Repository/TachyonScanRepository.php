<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Override;
use Doctrine\ORM\EntityRepository;
use Stu\Component\Ship\System\Type\TachyonScannerShipSystem;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\TachyonScan;
use Stu\Orm\Entity\TachyonScanInterface;

/**
 * @extends EntityRepository<TachyonScan>
 */
final class TachyonScanRepository extends EntityRepository implements TachyonScanRepositoryInterface
{
    #[Override]
    public function prototype(): TachyonScanInterface
    {
        return new TachyonScan();
    }

    #[Override]
    public function isTachyonScanActiveByShipLocationAndOwner(ShipInterface $ship): bool
    {
        $isSystem = $ship->getSystem() !== null;

        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT count(ts.id)
                FROM %s ts
                JOIN %s mf
                WITH ts.%s = mf.id
                WHERE ts.scan_time > :theTime
                AND mf.id = :mapId
                AND ts.user_id = :userId',
                TachyonScan::class,
                $isSystem ? StarSystemMap::class : Map::class,
                $isSystem ? 'starsystem_map_id' : 'map_id'
            )
        )->setParameters([
            'mapId' => $ship->getCurrentMapField()->getId(),
            'theTime' => time() - TachyonScannerShipSystem::DECLOAK_INTERVAL,
            'userId' => $ship->getUser()->getId()
        ])->getSingleScalarResult() > 0;
    }

    #[Override]
    public function save(TachyonScanInterface $obj): void
    {
        $em = $this->getEntityManager();

        $em->persist($obj);
    }

    #[Override]
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
