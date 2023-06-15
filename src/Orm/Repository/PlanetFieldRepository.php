<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Component\Building\BuildingEnum;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\BuildingCommodity;
use Stu\Orm\Entity\BuildingFunction;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\PlanetFieldInterface;

/**
 * @extends EntityRepository<PlanetField>
 */
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

    public function getByColonyAndType(int $colonyId, int $planetFieldTypeId): array
    {
        return $this->findBy([
            'colonies_id' => $colonyId,
            'type_id' => $planetFieldTypeId,
        ]);
    }

    public function getEnergyConsumingByColony(
        int $colonyId,
        array $state = [0, 1],
        ?int $limit = null,
        array $excludedFields = [-1]
    ): iterable {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT f FROM %s f
                WHERE f.colonies_id = :colonyId
                AND f.aktiv IN (:state)
                AND f.field_id NOT IN (:excluded)
                AND f.buildings_id IN (
                    SELECT b.id FROM %s b WHERE b.eps_proc < 0
                )',
                PlanetField::class,
                Building::class
            )
        )->setParameters([
            'colonyId' => $colonyId,
            'state' => $state,
            'excluded' => $excludedFields
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

    public function getWorkerConsumingByColonyAndState(
        int $colonyId,
        array $state = [0, 1],
        ?int $limit = null,
        array $excludedFields = [-1]
    ): iterable {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT f FROM %s f
                WHERE f.colonies_id = :colonyId
                AND f.aktiv IN (:state)
                AND f.field_id NOT IN (:excluded)
                AND f.buildings_id IN (
                    SELECT b.id FROM %s b WHERE b.bev_use > 0
                )',
                PlanetField::class,
                Building::class
            )
        )->setParameters([
            'colonyId' => $colonyId,
            'state' => $state,
            'excluded' => $excludedFields
        ])
            ->setMaxResults($limit)
            ->getResult();
    }

    public function getCommodityConsumingByColonyAndCommodity(
        int $colonyId,
        int $commodityId,
        array $state = [0, 1],
        ?int $limit = null,
        array $excludedFields = [-1]
    ): iterable {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT f FROM %s f
                WHERE f.colonies_id = :colonyId
                AND f.field_id NOT IN (:excluded)
                AND f.buildings_id IN (
                    SELECT bg.buildings_id FROM %s bg WHERE bg.commodity_id = :commodityId AND bg.count < 0
                ) AND f.aktiv IN (:state)',
                PlanetField::class,
                BuildingCommodity::class
            )
        )->setParameters([
            'colonyId' => $colonyId,
            'commodityId' => $commodityId,
            'state' => $state,
            'excluded' => $excludedFields
        ])
            ->setMaxResults($limit)
            ->getResult();
    }

    public function getCommodityProducingByColonyAndCommodity(int $colonyId, int $commodityId): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT f FROM %s f WHERE f.colonies_id = :colonyId AND f.buildings_id IN (
                    SELECT bg.buildings_id FROM %s bg WHERE bg.commodity_id = :commodityId AND bg.count > 0
                )',
                PlanetField::class,
                BuildingCommodity::class
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
        return (int) $this->getEntityManager()->createQuery(
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
        return (int) $this->getEntityManager()->createQuery(
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

    public function getByColonyAndBuildingFunction(
        int $colonyId,
        array $buildingFunctionIds
    ): array {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT f FROM %s f WHERE f.colonies_id = :colonyId AND f.buildings_id IN (
                        SELECT bf.buildings_id FROM %s bf WHERE bf.function IN (:buildingFunctionId)
                    )',
                    PlanetField::class,
                    BuildingFunction::class
                )
            )
            ->setParameters([
                'colonyId' => $colonyId,
                'buildingFunctionId' => $buildingFunctionIds
            ])
            ->getResult();
    }

    public function getMaxShieldsOfColony(int $colonyId): int
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('capacity', 'capacity');

        return (int) $this->getEntityManager()->createNativeQuery(
            'SELECT COUNT(distinct f1.id) * :generatorCapacity + COUNT(distinct f2.id) * :batteryCapacity as capacity
            FROM stu_colonies_fielddata f1
            LEFT JOIN stu_colonies_fielddata f2
            ON f1.colonies_id = f2.colonies_id
            AND f2.aktiv = 1 AND f2.buildings_id IN (
                SELECT bf2.buildings_id FROM stu_buildings_functions bf2 WHERE bf2.function = :shieldBattery
            )
            WHERE f1.colonies_id = :colonyId AND f1.aktiv = 1 AND f1.buildings_id IN (
                SELECT bf1.buildings_id FROM stu_buildings_functions bf1 WHERE bf1.function = :shieldGenerator
            )',
            $rsm
        )->setParameters([
            'colonyId' => $colonyId,
            'shieldGenerator' => BuildingEnum::BUILDING_FUNCTION_SHIELD_GENERATOR,
            'shieldBattery' => BuildingEnum::BUILDING_FUNCTION_SHIELD_BATTERY,
            'generatorCapacity' => BuildingEnum::SHIELD_GENERATOR_CAPACITY,
            'batteryCapacity' => BuildingEnum::SHIELD_BATTERY_CAPACITY
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

    public function getByColonyWithBuilding(int $colonyId): array
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

    public function getEnergyProductionByColony(int $colonyId, array $excludedFields = [-1]): int
    {
        return (int) $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT SUM(b.eps_proc)
                FROM %s cfd
                LEFT JOIN %s b WITH b.id = cfd.buildings_id
                WHERE cfd.aktiv = :state
                AND cfd.field_id NOT IN (:excluded)
                AND cfd.colonies_id = :colonyId',
                PlanetField::class,
                Building::class
            )
        )->setParameters([
            'state' => 1,
            'colonyId' => $colonyId,
            'excluded' => $excludedFields
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
