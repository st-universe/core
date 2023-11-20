<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Component\Building\BuildingEnum;
use Stu\Lib\Colony\PlanetFieldHostInterface;
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
            'id' => $fieldId
        ]);
    }

    public function getByColonyAndFieldIndex(int $colonyId, int $fieldIndex): ?PlanetFieldInterface
    {
        return $this->findOneBy([
            'colonies_id' => $colonyId,
            'field_id' => $fieldIndex
        ]);
    }

    public function getByColonyAndType(int $colonyId, int $planetFieldTypeId): array
    {
        return $this->findBy([
            'colonies_id' => $colonyId,
            'type_id' => $planetFieldTypeId,
        ]);
    }

    public function getEnergyConsumingByHost(
        PlanetFieldHostInterface $host,
        array $state = [0, 1],
        ?int $limit = null,
        array $excludedFields = [-1]
    ): array {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT f FROM %s f
                WHERE f.%s = :hostId
                AND f.aktiv IN (:state)
                AND f.field_id NOT IN (:excluded)
                AND f.buildings_id IN (
                    SELECT b.id FROM %s b WHERE b.eps_proc < 0
                )',
                PlanetField::class,
                $host->getPlanetFieldHostColumnIdentifier(),
                Building::class
            )
        )->setParameters([
            'hostId' => $host->getId(),
            'state' => $state,
            'excluded' => $excludedFields
        ])
            ->setMaxResults($limit)
            ->getResult();
    }

    public function getEnergyProducingByHost(PlanetFieldHostInterface $host): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT f FROM %s f
                WHERE f.%s = :hostId AND f.buildings_id IN (
                    SELECT b.id FROM %s b WHERE b.eps_proc > 0
                )',
                PlanetField::class,
                $host->getPlanetFieldHostColumnIdentifier(),
                Building::class
            )
        )->setParameters([
            'hostId' => $host->getId()
        ])->getResult();
    }

    public function getHousingProvidingByHost(PlanetFieldHostInterface $host): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT f FROM %s f
                WHERE f.%s = :hostId AND f.buildings_id IN (
                    SELECT b.id FROM %s b WHERE b.bev_pro > 0
                )',
                PlanetField::class,
                $host->getPlanetFieldHostColumnIdentifier(),
                Building::class
            )
        )->setParameters([
            'hostId' => $host->getId()
        ])->getResult();
    }

    public function getWorkerConsumingByHost(PlanetFieldHostInterface $host): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT f FROM %s f
                WHERE f.%s = :hostId AND f.buildings_id IN (
                    SELECT b.id FROM %s b WHERE b.bev_use > 0
                )',
                PlanetField::class,
                $host->getPlanetFieldHostColumnIdentifier(),
                Building::class
            )
        )->setParameters([
            'hostId' => $host->getId(),
        ])->getResult();
    }

    public function getWorkerConsumingByColonyAndState(
        int $colonyId,
        array $state = [0, 1],
        ?int $limit = null,
        array $excludedFields = [-1]
    ): array {
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

    public function getCommodityConsumingByHostAndCommodity(
        PlanetFieldHostInterface $host,
        int $commodityId,
        array $state = [0, 1],
        ?int $limit = null,
        array $excludedFields = [-1]
    ): array {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT f FROM %s f
                WHERE f.%s = :hostId
                AND f.field_id NOT IN (:excluded)
                AND f.buildings_id IN (
                    SELECT bg.buildings_id FROM %s bg WHERE bg.commodity_id = :commodityId AND bg.count < 0
                ) AND f.aktiv IN (:state)',
                PlanetField::class,
                $host->getPlanetFieldHostColumnIdentifier(),
                BuildingCommodity::class
            )
        )->setParameters([
            'hostId' => $host->getId(),
            'commodityId' => $commodityId,
            'state' => $state,
            'excluded' => $excludedFields
        ])
            ->setMaxResults($limit)
            ->getResult();
    }

    public function getCommodityProducingByHostAndCommodity(PlanetFieldHostInterface $host, int $commodityId): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT f FROM %s f
                WHERE f.%s = :hostId AND f.buildings_id IN (
                    SELECT bg.buildings_id FROM %s bg WHERE bg.commodity_id = :commodityId AND bg.count > 0
                )',
                PlanetField::class,
                $host->getPlanetFieldHostColumnIdentifier(),
                BuildingCommodity::class
            )
        )->setParameters([
            'hostId' => $host->getId(),
            'commodityId' => $commodityId,
        ])->getResult();
    }

    public function getCountByHostAndBuilding(PlanetFieldHostInterface $host, int $buildingId): int
    {
        return $this->count([
            $host->getPlanetFieldHostColumnIdentifier() => $host->getId(),
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
        PlanetFieldHostInterface $host,
        array $buildingFunctionIds,
        array $state
    ): int {
        return (int) $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT COUNT(f) FROM %s f
                WHERE f.%s = :host AND f.aktiv IN(:state) AND f.buildings_id IN (
                    SELECT bf.buildings_id FROM %s bf WHERE bf.function IN (:buildingFunctionIds)
                )',
                PlanetField::class,
                $host->getPlanetFieldHostIdentifier(),
                BuildingFunction::class
            )
        )->setParameters([
            'host' => $host,
            'buildingFunctionIds' => $buildingFunctionIds,
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

    public function getMaxShieldsOfColony(PlanetFieldHostInterface $host): int
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('capacity', 'capacity');

        return (int) $this->getEntityManager()->createNativeQuery(
            sprintf(
                'SELECT COUNT(distinct f1.id) * :generatorCapacity + COUNT(distinct f2.id) * :batteryCapacity as capacity
            FROM stu_colonies_fielddata f1
            LEFT JOIN stu_colonies_fielddata f2
            ON f1.colonies_id = f2.colonies_id
            AND f2.aktiv = 1 AND f2.buildings_id IN (
                SELECT bf2.buildings_id FROM stu_buildings_functions bf2 WHERE bf2.function = :shieldBattery
            )
            WHERE f1.%s = :hostId AND f1.aktiv = 1 AND f1.buildings_id IN (
                SELECT bf1.buildings_id FROM stu_buildings_functions bf1 WHERE bf1.function = :shieldGenerator
            )',
                $host->getPlanetFieldHostColumnIdentifier()
            ),
            $rsm
        )->setParameters([
            'hostId' => $host->getId(),
            'shieldGenerator' => BuildingEnum::BUILDING_FUNCTION_SHIELD_GENERATOR,
            'shieldBattery' => BuildingEnum::BUILDING_FUNCTION_SHIELD_BATTERY,
            'generatorCapacity' => BuildingEnum::SHIELD_GENERATOR_CAPACITY,
            'batteryCapacity' => BuildingEnum::SHIELD_BATTERY_CAPACITY
        ])->getSingleScalarResult();
    }

    public function getInConstructionByUser(int $userId): array
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

    public function getByConstructionFinish(int $finishTime): array
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

    public function getByColonyWithBuilding(PlanetFieldHostInterface $host): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT f FROM %s f
                JOIN %s b
                WITH f.buildings_id = b.id
                WHERE f.%s = :hostId
                AND f.buildings_id > 0
                ORDER BY b.name ASC',
                PlanetField::class,
                Building::class,
                $host->getPlanetFieldHostColumnIdentifier(),
            )
        )->setParameters([
            'hostId' => $host->getId(),
        ])->getResult();
    }

    public function getEnergyProductionByColony(
        PlanetFieldHostInterface $host,
        array $excludedFields = [-1]
    ): int {
        return (int) $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT SUM(b.eps_proc)
                FROM %s cfd
                LEFT JOIN %s b WITH b.id = cfd.buildings_id
                WHERE cfd.aktiv = :state
                AND cfd.field_id NOT IN (:excluded)
                AND cfd.%s = :host',
                PlanetField::class,
                Building::class,
                $host->getPlanetFieldHostIdentifier()
            )
        )->setParameters([
            'state' => 1,
            'host' => $host,
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
