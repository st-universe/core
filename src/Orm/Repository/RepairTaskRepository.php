<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Override;
use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\RepairTask;
use Stu\Orm\Entity\RepairTaskInterface;
use Stu\Orm\Entity\ShipInterface;

/**
 * @extends EntityRepository<RepairTask>
 */
final class RepairTaskRepository extends EntityRepository implements RepairTaskRepositoryInterface
{
    #[Override]
    public function prototype(): RepairTaskInterface
    {
        return new RepairTask();
    }

    #[Override]
    public function save(RepairTaskInterface $obj): void
    {
        $em = $this->getEntityManager();

        $em->persist($obj);
    }

    #[Override]
    public function delete(RepairTaskInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        //$em->flush();
    }

    #[Override]
    public function getByShip(int $shipId): ?ShipInterface
    {
        return $this->findOneBy([
            'ship_id' => $shipId
        ]);
    }

    #[Override]
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

    #[Override]
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
