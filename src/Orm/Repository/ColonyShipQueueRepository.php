<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ColonyShipQueue;
use Stu\Orm\Entity\ColonyShipQueueInterface;

/**
 * @extends EntityRepository<ColonyShipQueue>
 */
final class ColonyShipQueueRepository extends EntityRepository implements ColonyShipQueueRepositoryInterface
{
    public function prototype(): ColonyShipQueueInterface
    {
        return new ColonyShipQueue();
    }

    public function save(ColonyShipQueueInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    public function delete(ColonyShipQueueInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
    }

    public function stopQueueByColonyAndBuildingFunction(int $colonyId, int $buildingFunctionId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'UPDATE %s sq SET sq.stop_date = :time WHERE sq.colony_id = :colonyId AND sq.building_function_id = :buildingFunctionId',
                    ColonyShipQueue::class
                )
            )
            ->setParameters([
                'time' => time(),
                'colonyId' => $colonyId,
                'buildingFunctionId' => $buildingFunctionId
            ])
            ->execute();
    }

    public function restartQueueByColonyAndBuildingFunction(int $colonyId, int $buildingFunctionId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'UPDATE %s sq SET sq.finish_date = (:time - sq.stop_date + sq.finish_date), sq.stop_date = :stopDate WHERE sq.colony_id = :colonyId AND sq.building_function_id = :buildingFunctionId',
                    ColonyShipQueue::class
                )
            )
            ->setParameters([
                'stopDate' => 0,
                'time' => time(),
                'colonyId' => $colonyId,
                'buildingFunctionId' => $buildingFunctionId
            ])
            ->execute();
    }

    public function getAmountByColonyAndBuildingFunction(int $colonyId, int $buildingFunctionId): int
    {
        return $this->count([
            'colony_id' => $colonyId,
            'building_function_id' => $buildingFunctionId
        ]);
    }

    public function getByColony(int $colonyId): array
    {
        return $this->findBy([
            'colony_id' => $colonyId
        ]);
    }

    public function getByUser(int $userId): array
    {
        return $this->findBy([
            'user_id' => $userId
        ]);
    }

    public function getCountByBuildplan(int $buildplanId): int
    {
        return $this->count([
            'buildplan_id' => $buildplanId
        ]);
    }

    public function getFinishedJobs(): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT sq FROM %s sq WHERE sq.stop_date = :stopDate AND sq.finish_date <= :time',
                    ColonyShipQueue::class
                )
            )
            ->setParameters([
                'stopDate' => 0,
                'time' => time()
            ])
            ->getResult();
    }

    public function truncateByColony(ColonyInterface $colony): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s sq WHERE sq.colony_id = :colony',
                    ColonyShipQueue::class
                )
            )
            ->setParameters([
                'colony' => $colony
            ])
            ->execute();
    }

    public function truncateByColonyAndBuildingFunction(int $colonyId, int $buildingFunctionId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s sq WHERE sq.colony_id = :colonyId AND sq.building_function_id = :buildingFunctionId',
                    ColonyShipQueue::class
                )
            )
            ->setParameters([
                'colonyId' => $colonyId,
                'buildingFunctionId' => $buildingFunctionId
            ])
            ->execute();
    }
}
