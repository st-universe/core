<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\MiningQueue;

/**
 * @extends EntityRepository<MiningQueue>
 */
final class MiningQueueRepository extends EntityRepository implements MiningQueueRepositoryInterface
{
    #[Override]
    public function prototype(): MiningQueue
    {
        return new MiningQueue();
    }

    #[Override]
    public function getByShip(int $shipId): ?MiningQueue
    {
        return $this->findOneBy([
            'ship_id' => $shipId
        ]);
    }

    #[Override]
    public function save(MiningQueue $miningqueue): void
    {
        $em = $this->getEntityManager();

        $em->persist($miningqueue);
    }

    #[Override]
    public function delete(MiningQueue $miningqueue): void
    {
        $em = $this->getEntityManager();

        $em->remove($miningqueue);
    }

    #[Override]
    public function truncateByShipId(int $shipId): void
    {
        $q = $this->getEntityManager()->createQuery(
            sprintf(
                'delete from %s t where t.ship_id = :shipId',
                MiningQueue::class
            )
        );
        $q->setParameter('shipId', $shipId);
        $q->execute();
    }
}
