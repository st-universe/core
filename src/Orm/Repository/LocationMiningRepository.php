<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\MiningQueue;
use Stu\Orm\Entity\MiningQueueInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\LocationMining;
use Stu\Orm\Entity\LocationMiningInterface;

/**
 * @extends EntityRepository<LocationMining>
 */
final class LocationMiningRepository extends EntityRepository implements LocationMiningRepositoryInterface
{
    public const int ISM_RECREATION_COOLDOWN = 1728000; // 20 days

    #[Override]
    public function prototype(): LocationMiningInterface
    {
        return new LocationMining();
    }

    #[Override]
    public function save(LocationMiningInterface $locationMining): void
    {
        $em = $this->getEntityManager();
        $em->persist($locationMining);
    }

    #[Override]
    public function getMiningAtLocation(ShipInterface $ship): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT ml FROM %s ml
                 WHERE ml.location_id = :locationId',
                LocationMining::class
            )
        )->setParameters([
            'locationId' => $ship->getLocation()->getId()
        ])->getResult();
    }

    #[Override]
    public function getMiningQueueAtLocation(ShipInterface $ship): ?MiningQueueInterface
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT mq FROM %s mq
                 WHERE mq.ship_id = :shipId',
                MiningQueue::class
            )
        )->setParameters([
            'shipId' => $ship->getId()
        ])->getOneOrNullResult();
    }

    #[Override]
    public function findById(int $id): ?LocationMiningInterface
    {
        return $this->find($id);
    }

    /**
     * @return LocationMiningInterface[]
     */
    #[Override]
    public function findDepletedEntries(): array
    {
        $twentyDaysAgo = time() - self::ISM_RECREATION_COOLDOWN;

        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT lm FROM %s lm
                 WHERE lm.depleted_at < :twentyDaysAgo
                 AND lm.actual_amount < lm.max_amount',
                LocationMining::class
            )
        )->setParameters([
            'twentyDaysAgo' => $twentyDaysAgo
        ])->getResult();
    }
}
