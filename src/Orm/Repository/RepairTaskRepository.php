<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\RepairTask;
use Stu\Orm\Entity\RepairTaskInterface;
use Stu\Orm\Entity\ShipInterface;

/**
 * @extends EntityRepository<RepairTask>
 */
final class RepairTaskRepository extends EntityRepository implements RepairTaskRepositoryInterface
{
    public function prototype(): RepairTaskInterface
    {
        return new RepairTask();
    }

    public function save(RepairTaskInterface $obj): void
    {
        $em = $this->getEntityManager();

        $em->persist($obj);
    }

    public function delete(RepairTaskInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        //$em->flush();
    }

    public function getByShip(int $shipId): ?ShipInterface
    {
        return $this->findOneBy([
            'ship_id' => $shipId
        ]);
    }

    public function truncateByShipId(int $shipId): void
    {
        $q = $this->getEntityManager()->createQuery(
            sprintf(
                'delete from %s t where t.ship_id = :shipId',
                RepairTask::class
            )
        );
        $q->setParameter('shipId', $shipId);
        $q->execute();
    }

    public function getFinishedRepairTasks(): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT rt FROM %s rt
                WHERE rt.finish_time <= :actualTime',
                    RepairTask::class
                )
            )
            ->setParameters([
                'actualTime' => time()
            ])
            ->getResult();
    }
}
