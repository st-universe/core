<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Override;
use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\ShipyardShipQueue;
use Stu\Orm\Entity\ShipyardShipQueueInterface;

/**
 * @extends EntityRepository<ShipyardShipQueue>
 */
final class ShipyardShipQueueRepository extends EntityRepository implements ShipyardShipQueueRepositoryInterface
{
    #[Override]
    public function prototype(): ShipyardShipQueueInterface
    {
        return new ShipyardShipQueue();
    }

    #[Override]
    public function save(ShipyardShipQueueInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    #[Override]
    public function delete(ShipyardShipQueueInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        //$em->flush();
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
            'ship_id' => $stationId
        ]);
    }

    #[Override]
    public function getAmountByShipyard(int $shipId): int
    {
        return $this->count([
            'ship_id' => $shipId
        ]);
    }

    #[Override]
    public function stopQueueByShipyard(int $shipId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'UPDATE %s sq SET sq.stop_date = :time WHERE sq.ship_id = :shipId',
                    ShipyardShipQueue::class
                )
            )
            ->setParameters([
                'time' => time(),
                'shipId' => $shipId
            ])
            ->execute();
    }

    #[Override]
    public function restartQueueByShipyard(int $shipId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'UPDATE %s sq
                    SET sq.finish_date = (:time - sq.stop_date + sq.finish_date), sq.stop_date = :stopDate
                    WHERE sq.stop_date != 0
                    AND sq.ship_id = :shipId',
                    ShipyardShipQueue::class
                )
            )
            ->setParameters([
                'stopDate' => 0,
                'time' => time(),
                'shipId' => $shipId
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
