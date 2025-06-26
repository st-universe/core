<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\ShipyardShipQueue;

/**
 * @extends EntityRepository<ShipyardShipQueue>
 */
final class ShipyardShipQueueRepository extends EntityRepository implements ShipyardShipQueueRepositoryInterface
{
    #[Override]
    public function prototype(): ShipyardShipQueue
    {
        return new ShipyardShipQueue();
    }

    #[Override]
    public function save(ShipyardShipQueue $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    #[Override]
    public function delete(ShipyardShipQueue $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
    }

    #[Override]
    public function getByUser(int $userId): array
    {
        return $this->findBy([
            'user_id' => $userId
        ]);
    }

    #[Override]
    public function getByShipyard(int $stationId): array
    {
        return $this->findBy([
            'station_id' => $stationId
        ]);
    }

    #[Override]
    public function getAmountByShipyard(int $stationId): int
    {
        return $this->count([
            'station_id' => $stationId
        ]);
    }

    #[Override]
    public function stopQueueByShipyard(int $stationId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'UPDATE %s sq SET sq.stop_date = :time WHERE sq.station_id = :stationId',
                    ShipyardShipQueue::class
                )
            )
            ->setParameters([
                'time' => time(),
                'stationId' => $stationId
            ])
            ->execute();
    }

    #[Override]
    public function restartQueueByShipyard(int $stationId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'UPDATE %s sq
                    SET sq.finish_date = (:time - sq.stop_date + sq.finish_date), sq.stop_date = :stopDate
                    WHERE sq.stop_date != 0
                    AND sq.station_id = :stationId',
                    ShipyardShipQueue::class
                )
            )
            ->setParameters([
                'stopDate' => 0,
                'time' => time(),
                'stationId' => $stationId
            ])
            ->execute();
    }

    #[Override]
    public function getFinishedJobs(): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT sq FROM %s sq WHERE sq.stop_date = :stopDate AND sq.finish_date <= :time',
                    ShipyardShipQueue::class
                )
            )
            ->setParameters([
                'stopDate' => 0,
                'time' => time()
            ])
            ->getResult();
    }
}
