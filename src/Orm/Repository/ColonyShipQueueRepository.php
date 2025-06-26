<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyShipQueue;

/**
 * @extends EntityRepository<ColonyShipQueue>
 */
final class ColonyShipQueueRepository extends EntityRepository implements ColonyShipQueueRepositoryInterface
{
    #[Override]
    public function prototype(): ColonyShipQueue
    {
        return new ColonyShipQueue();
    }

    #[Override]
    public function save(ColonyShipQueue $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    #[Override]
    public function delete(ColonyShipQueue $queue): void
    {
        $em = $this->getEntityManager();

        $ship = $queue->getShip();
        if ($ship !== null) {
            $ship->setColonyShipQueue(null);
        }

        $em->remove($queue);
        $em->flush(); //TODO really neccessary?
    }

    #[Override]
    public function stopQueueByColonyAndBuildingFunction(int $colonyId, BuildingFunctionEnum $buildingFunction): void
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
                'buildingFunctionId' => $buildingFunction->value
            ])
            ->execute();
    }

    #[Override]
    public function restartQueueByColonyAndBuildingFunction(int $colonyId, BuildingFunctionEnum $buildingFunction): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'UPDATE %s sq
                    SET sq.finish_date = (:time - sq.stop_date + sq.finish_date), sq.stop_date = :stopDate
                    WHERE sq.stop_date != 0
                    AND sq.colony_id = :colonyId
                    AND sq.building_function_id = :buildingFunctionId',
                    ColonyShipQueue::class
                )
            )
            ->setParameters([
                'stopDate' => 0,
                'time' => time(),
                'colonyId' => $colonyId,
                'buildingFunctionId' => $buildingFunction->value
            ])
            ->execute();
    }

    #[Override]
    public function getAmountByColonyAndBuildingFunctionAndMode(int $colonyId, BuildingFunctionEnum $buildingFunction, int $mode): int
    {
        return $this->count([
            'colony_id' => $colonyId,
            'building_function_id' => $buildingFunction->value,
            'mode' => $mode
        ]);
    }

    #[Override]
    public function getByColony(int $colonyId): array
    {
        return $this->findBy([
            'colony_id' => $colonyId
        ]);
    }

    #[Override]
    public function getByColonyAndMode(int $colonyId, int $mode): array
    {
        return $this->findBy([
            'colony_id' => $colonyId,
            'mode' => $mode
        ]);
    }

    #[Override]
    public function getByUser(int $userId): array
    {
        return $this->findBy([
            'user_id' => $userId
        ]);
    }

    #[Override]
    public function getByUserAndMode(int $userId, int $mode): array
    {
        return $this->findBy([
            'user_id' => $userId,
            'mode' => $mode
        ]);
    }

    #[Override]
    public function getCountByBuildplan(int $buildplanId): int
    {
        return $this->count([
            'buildplan_id' => $buildplanId
        ]);
    }

    #[Override]
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

    #[Override]
    public function truncateByColony(Colony $colony): void
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

    #[Override]
    public function truncateByColonyAndBuildingFunction(Colony $colony, BuildingFunctionEnum $buildingFunction): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s sq WHERE sq.colony = :colony AND sq.building_function_id = :buildingFunctionId',
                    ColonyShipQueue::class
                )
            )
            ->setParameters([
                'colony' => $colony,
                'buildingFunctionId' => $buildingFunction->value
            ])
            ->execute();
    }

    #[Override]
    public function truncateByShip(int $shipId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s sq WHERE sq.ship_id = :shipId',
                    ColonyShipQueue::class
                )
            )
            ->setParameters([
                'shipId' => $shipId
            ])
            ->execute();
    }
}
