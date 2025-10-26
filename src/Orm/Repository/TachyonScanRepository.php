<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Component\Spacecraft\System\Type\TachyonScannerShipSystem;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\TachyonScan;

/**
 * @extends EntityRepository<TachyonScan>
 */
final class TachyonScanRepository extends EntityRepository implements TachyonScanRepositoryInterface
{
    #[\Override]
    public function prototype(): TachyonScan
    {
        return new TachyonScan();
    }

    #[\Override]
    public function isTachyonScanActiveByShipLocationAndOwner(Spacecraft $spacecraft): bool
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT count(ts.id)
                FROM %s ts
                JOIN %s l
                WITH ts.location_id = l.id
                WHERE ts.scan_time > :theTime
                AND l.id = :locationId
                AND ts.user_id = :userId',
                TachyonScan::class,
                Location::class
            )
        )->setParameters([
            'locationId' => $spacecraft->getLocation()->getId(),
            'theTime' => time() - TachyonScannerShipSystem::DECLOAK_INTERVAL,
            'userId' => $spacecraft->getUser()->getId()
        ])->getSingleScalarResult() > 0;
    }

    #[\Override]
    public function save(TachyonScan $obj): void
    {
        $em = $this->getEntityManager();

        $em->persist($obj);
    }

    #[\Override]
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
