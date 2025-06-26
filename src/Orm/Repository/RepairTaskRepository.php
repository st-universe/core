<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\RepairTask;
use Stu\Orm\Entity\Ship;

/**
 * @extends EntityRepository<RepairTask>
 */
final class RepairTaskRepository extends EntityRepository implements RepairTaskRepositoryInterface
{
    #[Override]
    public function prototype(): RepairTask
    {
        return new RepairTask();
    }

    #[Override]
    public function save(RepairTask $obj): void
    {
        $em = $this->getEntityManager();

        $em->persist($obj);
    }

    #[Override]
    public function delete(RepairTask $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
    }

    #[Override]
    public function getByShip(int $shipId): ?RepairTask
    {
        return $this->findOneBy([
            'spacecraft_id' => $shipId
        ]);
    }

    #[Override]
    public function truncateByShipId(int $shipId): void
    {
        $q = $this->getEntityManager()->createQuery(
            sprintf(
                'delete from %s t where t.spacecraft_id = :shipId',
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
