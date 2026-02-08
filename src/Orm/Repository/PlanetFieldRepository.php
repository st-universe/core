<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Colony\Shields\ColonyShieldingManager;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\BuildingCommodity;
use Stu\Orm\Entity\BuildingFunction;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\PlanetField;

/**
 * @extends EntityRepository<PlanetField>
 */
final class PlanetFieldRepository extends EntityRepository implements PlanetFieldRepositoryInterface
{
    #[\Override]
    public function prototype(): PlanetField
    {
        return new PlanetField();
    }

    #[\Override]
    public function save(PlanetField $planetField): void
    {
        $em = $this->getEntityManager();

        $em->persist($planetField);
    }

    #[\Override]
    public function delete(PlanetField $planetField): void
    {
        $em = $this->getEntityManager();

        $em->remove($planetField);
        $em->flush();
    }

    #[\Override]
    public function getByColonyAndFieldId(int $colonyId, int $fieldId): ?PlanetField
    {
        return $this->findOneBy([
            'colony_id' => $colonyId,
            'id' => $fieldId
        ]);
    }

    #[\Override]
    public function getByColonyAndFieldIndex(int $colonyId, int $fieldIndex): ?PlanetField
    {
        return $this->findOneBy([
            'colony_id' => $colonyId,
            'field_id' => $fieldIndex
        ]);
    }

    #[\Override]
    public function getByColonyAndType(int $colonyId, int $planetFieldTypeId): array
    {
        return $this->findBy([
            'colony_id' => $colonyId,
            'type_id' => $planetFieldTypeId,
        ]);
    }

    #[\Override]
    public function getEnergyConsumingByHost(
        PlanetFieldHostInterface $host,
        array $state = [0, 1],
        ?int $limit = null,
        array $excludedFields = [-1]
    ): array {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT f FROM %s f
                JOIN f.building b
                WHERE f.%s = :hostId
                AND f.aktiv IN (:state)
                AND f.field_id NOT IN (:excluded)
                AND  b.eps_proc < 0',
                PlanetField::class,
                $host->getHostType()->getPlanetFieldHostColumnIdentifier()
            )
        )->setParameters([
            'hostId' => $host->getId(),
            'state' => $state,
            'excluded' => $excludedFields
        ])
            ->setMaxResults($limit)
            ->getResult();
    }

    #[\Override]
    public function getEnergyProducingByHost(PlanetFieldHostInterface $host): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT f FROM %s f
                JOIN f.building b
                WHERE f.%s = :hostId
                AND b.eps_proc > 0',
                PlanetField::class,
                $host->getHostType()->getPlanetFieldHostColumnIdentifier()
            )
        )->setParameters([
            'hostId' => $host->getId()
        ])->getResult();
    }

    #[\Override]
    public function getHousingProvidingByHost(PlanetFieldHostInterface $host): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT f FROM %s f
                JOIN f.building b
                WHERE f.%s = :hostId
                AND b.bev_pro > 0',
                PlanetField::class,
                $host->getHostType()->getPlanetFieldHostColumnIdentifier()
            )
        )->setParameters([
            'hostId' => $host->getId()
        ])->getResult();
    }

    #[\Override]
    public function getWorkerConsumingByHost(PlanetFieldHostInterface $host): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT f FROM %s f
                JOIN f.building b
                WHERE f.%s = :hostId
                AND b.bev_use > 0',
                PlanetField::class,
                $host->getHostType()->getPlanetFieldHostColumnIdentifier()
            )
        )->setParameters([
            'hostId' => $host->getId(),
        ])->getResult();
    }

    #[\Override]
    public function getWorkerConsumingByColonyAndState(
        int $colonyId,
        array $state = [0, 1],
        ?int $limit = null,
        array $excludedFields = [-1]
    ): array {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT f FROM %s f
                JOIN f.colony c
                JOIN f.building b
                WHERE c.id = :colonyId
                AND f.aktiv IN (:state)
                AND f.field_id NOT IN (:excluded)
                AND b.bev_use > 0',
                PlanetField::class
            )
        )->setParameters([
            'colonyId' => $colonyId,
            'state' => $state,
            'excluded' => $excludedFields
        ])
            ->setMaxResults($limit)
            ->getResult();
    }

    #[\Override]
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
                JOIN f.building b
                WHERE f.%s = :hostId
                AND f.field_id NOT IN (:excluded)
                AND b.id IN (
                    SELECT bg.buildings_id FROM %s bg WHERE bg.commodity_id = :commodityId AND bg.count < 0
                ) AND f.aktiv IN (:state)',
                PlanetField::class,
                $host->getHostType()->getPlanetFieldHostColumnIdentifier(),
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

    #[\Override]
    public function getCommodityProducingByHostAndCommodity(PlanetFieldHostInterface $host, int $commodityId): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT f FROM %s f
                JOIN f.building b
                WHERE f.%s = :hostId AND b.id IN (
                    SELECT bg.buildings_id FROM %s bg WHERE bg.commodity_id = :commodityId AND bg.count > 0
                )',
                PlanetField::class,
                $host->getHostType()->getPlanetFieldHostColumnIdentifier(),
                BuildingCommodity::class
            )
        )->setParameters([
            'hostId' => $host->getId(),
            'commodityId' => $commodityId,
        ])->getResult();
    }

    #[\Override]
    public function getCountByHostAndBuilding(PlanetFieldHostInterface $host, int $buildingId): int
    {
        return (int) $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT COUNT(f) FROM %s f
                JOIN f.building b
                WHERE f.%s = :hostId AND b.id = :buildingId',
                PlanetField::class,
                $host->getHostType()->getPlanetFieldHostColumnIdentifier()
            )
        )->setParameters([
            'hostId' => $host->getId(),
            'buildingId' => $buildingId,
        ])->getSingleScalarResult();
    }

    #[\Override]
    public function getCountByBuildingAndUser(int $buildingId, int $userId): int
    {
        return (int) $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT COUNT(f)
                FROM %s f
                JOIN f.building b
                WHERE b.id = :buildingId AND f.colony IN (
                    SELECT c FROM %s c WHERE c.user_id = :userId
                )',
                PlanetField::class,
                Colony::class
            )
        )->setParameters([
            'buildingId' => $buildingId,
            'userId' => $userId,
        ])->getSingleScalarResult();
    }

    #[\Override]
    public function getCountByColonyAndBuildingFunctionAndState(
        PlanetFieldHostInterface $host,
        array $buildingFunctions,
        array $state,
        array $ignoredFieldIds = []
    ): int {
        return (int) $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT COUNT(pf) FROM %s pf
                JOIN pf.building b
                WHERE pf.%s = :host
                AND pf.field_id NOT IN(:ignoredIds)
                AND pf.aktiv IN(:state) AND b.id IN (
                    SELECT bf.buildings_id FROM %s bf WHERE bf.function IN (:buildingFunctionIds)
                )',
                PlanetField::class,
                $host->getHostType()->getPlanetFieldHostIdentifier(),
                BuildingFunction::class
            )
        )->setParameters([
            'host' => $host,
            'ignoredIds' => $ignoredFieldIds === [] ? [-1] : $ignoredFieldIds,
            'buildingFunctionIds' => array_map(fn(BuildingFunctionEnum $function): int => $function->value, $buildingFunctions),
            'state' => $state,
        ])->getSingleScalarResult();
    }

    #[\Override]
    public function getByColonyAndBuildingFunction(
        int $colonyId,
        array $buildingFunctionIds
    ): array {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                'SELECT f FROM %s f
                    JOIN f.colony c
                    JOIN f.building b
                    WHERE c.id = :colonyId AND b.id IN (
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

    #[\Override]
    public function getMaxShieldsOfHost(PlanetFieldHostInterface $host): int
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('capacity', 'capacity');

        return (int) $this->getEntityManager()->createNativeQuery(
            sprintf(
                'SELECT COUNT(distinct f1.id) * :generatorCapacity + COUNT(distinct f2.id) * :batteryCapacity as capacity
            FROM stu_colonies_fielddata f1
            LEFT JOIN stu_colonies_fielddata f2
            ON f1.colony_id = f2.colony_id
            AND f2.aktiv = 1 AND f2.buildings_id IN (
                SELECT bf2.buildings_id FROM stu_buildings_functions bf2 WHERE bf2.function = :shieldBattery
            )
            WHERE f1.%s = :hostId AND f1.aktiv = 1 AND f1.buildings_id IN (
                SELECT bf1.buildings_id FROM stu_buildings_functions bf1 WHERE bf1.function = :shieldGenerator
            )',
                $host->getHostType()->getPlanetFieldHostColumnIdentifier()
            ),
            $rsm
        )->setParameters([
            'hostId' => $host->getId(),
            'shieldGenerator' => BuildingFunctionEnum::SHIELD_GENERATOR->value,
            'shieldBattery' => BuildingFunctionEnum::SHIELD_BATTERY->value,
            'generatorCapacity' => ColonyShieldingManager::SHIELD_GENERATOR_CAPACITY,
            'batteryCapacity' => ColonyShieldingManager::SHIELD_BATTERY_CAPACITY
        ])->getSingleScalarResult();
    }

    #[\Override]
    public function getInConstructionByUser(int $userId): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT f FROM %s f WHERE f.aktiv > 1 AND f.colony IN (
                    SELECT c FROM %s c WHERE c.user_id = :userId
                ) ORDER BY f.aktiv',
                PlanetField::class,
                Colony::class
            )
        )->setParameters([
            'userId' => $userId,
        ])->getResult();
    }

    #[\Override]
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

    #[\Override]
    public function getByColonyWithBuilding(PlanetFieldHostInterface $host): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT f FROM %s f
                JOIN f.building b
                WHERE f.%s = :hostId
                AND f.building IS NOT NULL
                ORDER BY b.name ASC',
                PlanetField::class,
                $host->getHostType()->getPlanetFieldHostColumnIdentifier(),
            )
        )->setParameters([
            'hostId' => $host->getId(),
        ])->getResult();
    }

    #[\Override]
    public function getEnergyProductionByHost(
        PlanetFieldHostInterface $host,
        array $excludedFields = [-1]
    ): int {
        return (int) $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT SUM(b.eps_proc)
                FROM %s cfd
                LEFT JOIN cfd.building b
                WHERE cfd.aktiv = :state
                AND cfd.field_id NOT IN (:excluded)
                AND cfd.%s = :host',
                PlanetField::class,
                $host->getHostType()->getPlanetFieldHostIdentifier()
            )
        )->setParameters([
            'state' => 1,
            'host' => $host,
            'excluded' => $excludedFields
        ])->getSingleScalarResult();
    }

    #[\Override]
    public function truncateByColony(Colony $colony): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s pf
                WHERE pf.colony = :colony',
                PlanetField::class
            )
        )
            ->setParameters(['colony' => $colony])
            ->execute();
    }
}
