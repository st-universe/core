<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\BuildingFunction;
use Stu\Orm\Entity\BuildingGood;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\PlanetFieldInterface;

final class PlanetFieldRepository extends EntityRepository implements PlanetFieldRepositoryInterface
{

    public function prototype(): PlanetFieldInterface
    {
        return new PlanetField();
    }

    public function save(PlanetFieldInterface $planetField): void
    {
        $em = $this->getEntityManager();

        $em->persist($planetField);
        $em->flush();
    }

    public function delete(PlanetFieldInterface $planetField): void
    {
        $em = $this->getEntityManager();

        $em->remove($planetField);
        $em->flush();
    }

    public function getByColonyAndFieldId(int $colonyId, int $fieldId): ?PlanetFieldInterface
    {
        return $this->findOneBy([
            'colonies_id' => $colonyId,
            'field_id' => $fieldId,
        ]);
    }

    public function getByColonyAndType(int $colonyId, int $planetFieldTypeId): iterable
    {
        return $this->findBy([
            'colonies_id' => $colonyId,
            'type' => $planetFieldTypeId,
        ]);
    }

    public function getEnergyConsumingByColony(
        int $colonyId,
        array $state = [0, 1],
        ?int $limit = null
    ): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT f FROM %s f WHERE f.colonies_id = :colonyId AND f.buildings_id IN (
                    SELECT b.id FROM %s b WHERE b.eps_proc < 0
                ) AND f.aktiv IN (:state)',
                PlanetField::class,
                Building::class
            )
        )->setParameters([
            'colonyId' => $colonyId,
            'state' => $state
        ])
            ->setMaxResults($limit)
            ->getResult();
    }

    public function getEnergyProducingByColony(int $colonyId): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT f FROM %s f WHERE f.colonies_id = :colonyId AND f.buildings_id IN (
                    SELECT b.id FROM %s b WHERE b.eps_proc > 0
                )',
                PlanetField::class,
                Building::class
            )
        )->setParameters([
            'colonyId' => $colonyId,
        ])->getResult();
    }

    public function getHousingProvidingByColony(int $colonyId): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT f FROM %s f WHERE f.colonies_id = :colonyId AND f.buildings_id IN (
                    SELECT b.id FROM %s b WHERE b.bev_pro > 0
                )',
                PlanetField::class,
                Building::class
            )
        )->setParameters([
            'colonyId' => $colonyId,
        ])->getResult();
    }

    public function getWorkerConsumingByColony(int $colonyId): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT f FROM %s f WHERE f.colonies_id = :colonyId AND f.buildings_id IN (
                    SELECT b.id FROM %s b WHERE b.bev_use > 0
                )',
                PlanetField::class,
                Building::class
            )
        )->setParameters([
            'colonyId' => $colonyId,
        ])->getResult();
    }

    public function getCommodityConsumingByColonyAndCommodity(
        int $colonyId,
        int $commodityId,
        array $state = [0, 1],
        ?int $limit = null
    ): iterable {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT f FROM %s f WHERE f.colonies_id = :colonyId AND f.buildings_id IN (
                    SELECT bg.buildings_id FROM %s bg WHERE bg.goods_id = :commodityId AND bg.count < 0
                ) AND f.aktiv IN (:state)',
                PlanetField::class,
                BuildingGood::class
            )
        )->setParameters([
            'colonyId' => $colonyId,
            'commodityId' => $commodityId,
            'state' => $state
        ])
            ->setMaxResults($limit)
            ->getResult();
    }

    public function getCommodityProducingByColonyAndCommodity(int $colonyId, int $commodityId): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT f FROM %s f WHERE f.colonies_id = :colonyId AND f.buildings_id IN (
                    SELECT bg.buildings_id FROM %s bg WHERE bg.goods_id = :commodityId AND bg.count > 0
                )',
                PlanetField::class,
                BuildingGood::class
            )
        )->setParameters([
            'colonyId' => $colonyId,
            'commodityId' => $commodityId,
        ])->getResult();
    }

    public function getCountByColonyAndBuilding(int $colonyId, int $buildingId): int
    {
        return $this->count([
            'colonies_id' => $colonyId,
            'buildings_id' => $buildingId,
        ]);
    }

    public function getCountByBuildingAndUser(int $buildingId, int $userId): int
    {
        return (int)$this->getEntityManager()->createQuery(
            sprintf(
                'SELECT COUNT(f) FROM %s f WHERE f.buildings_id = :buildingId AND f.colonies_id IN (
                    SELECT c.id FROM %s c WHERE c.user_id = :userId
                )',
                PlanetField::class,
                Colony::class
            )
        )->setParameters([
            'buildingId' => $buildingId,
            'userId' => $userId,
        ])->getSingleScalarResult();
    }

    public function getCountByColonyAndBuildingFunctionAndState(
        int $colonyId,
        array $buildingFunctionIds,
        array $state
    ): int {
        return (int)$this->getEntityManager()->createQuery(
            sprintf(
                'SELECT COUNT(f) FROM %s f WHERE f.colonies_id = :colonyId AND f.aktiv IN(:state) AND f.buildings_id IN (
                    SELECT bf.buildings_id FROM %s bf WHERE bf.function IN (:buildingFunctionId)
                )',
                PlanetField::class,
                BuildingFunction::class
            )
        )->setParameters([
            'colonyId' => $colonyId,
            'buildingFunctionId' => $buildingFunctionIds,
            'state' => $state,
        ])->getSingleScalarResult();
    }

    public function getInConstructionByUser(int $userId): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT f FROM %s f WHERE f.aktiv > 1 AND f.colonies_id IN (
                    SELECT c.id FROM %s c WHERE c.user_id = :userId
                ) ORDER BY f.aktiv',
                PlanetField::class,
                Colony::class
            )
        )->setParameters([
            'userId' => $userId,
        ])->getResult();
    }

    public function getByConstructionFinish(int $finishTime): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT f FROM %s f WHERE f.aktiv BETWEEN 2 AND :finishTime',
                PlanetField::class
            )
        )->setParameters([
            'finishTime' => $finishTime,
        ])->getResult();
    }

    public function getByColonyWithBuilding(int $colonyId): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT f FROM %s f WHERE f.colonies_id = :colonyId AND f.buildings_id > 0',
                PlanetField::class
            )
        )->setParameters([
            'colonyId' => $colonyId,
        ])->getResult();
    }

    public function getByColony(int $colonyId): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT f FROM %s f INDEX BY f.field_id WHERE f.colonies_id = :colonyId ORDER BY f.field_id',
                PlanetField::class
            )
        )->setParameters([
            'colonyId' => $colonyId,
        ])->getResult();
    }

    public function getEnergyProductionByColony(int $colonyId): int
    {
        return (int)$this->getEntityManager()->createQuery(
            sprintf(
                'SELECT SUM(b.eps_proc) FROM %s cfd LEFT JOIN %s b WITH b.id = cfd.buildings_id WHERE
                cfd.aktiv = :state AND cfd.colonies_id = :colonyId',
                PlanetField::class,
                Building::class
            )
        )->setParameters([
            'state' => 1,
            'colonyId' => $colonyId,
        ])->getSingleScalarResult();
    }

    public function truncateByColony(ColonyInterface $colony): void
    {
       $this->getEntityManager()->createQuery(
           sprintf(
               'DELETE FROM %s pf WHERE pf.colonies_id = :colonyId',
               PlanetField::class
           )
       )
           ->setParameters(['colonyId' => $colony])
           ->execute();
    }
}
