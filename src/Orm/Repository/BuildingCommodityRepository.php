<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Component\Colony\ColonyFunctionManager;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Module\Logging\StuLogger;
use Stu\Orm\Entity\BuildingCommodity;
use Stu\Orm\Entity\ColonyClass;
use Stu\Orm\Entity\User;

/**
 * @extends EntityRepository<BuildingCommodity>
 */
final class BuildingCommodityRepository extends EntityRepository implements BuildingCommodityRepositoryInterface
{
    #[\Override]
    public function getByBuilding(int $buildingId): array
    {
        return $this->findBy([
            'buildings_id' => $buildingId
        ]);
    }

    #[\Override]
    public function getProductionByColony(PlanetFieldHostInterface $host, ColonyClass $colonyClass): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('commodity_id', 'commodity_id', 'integer');
        $rsm->addScalarResult('production', 'production', 'integer');
        $rsm->addScalarResult('pc', 'pc', 'integer');

        return $this->getEntityManager()
            ->createNativeQuery(
                sprintf(
                    'WITH production_data AS (
                        SELECT c.commodity_id, SUM(c.count) AS production
                        FROM stu_colonies_fielddata b
                        JOIN stu_buildings_commodity c ON c.buildings_id = b.buildings_id
                        WHERE b.%s = :hostId AND b.aktiv = :state
                        GROUP BY c.commodity_id
                    ),
                    planet_class_data AS (
                        SELECT commodity_id, count
                        FROM stu_planets_commodity
                        WHERE planet_classes_id = :colonyClassId
                    )
                    SELECT 
                        a.id AS commodity_id,
                        COALESCE(p.production, 0) AS production,
                        COALESCE(pcd.count, 0) AS pc
                    FROM stu_commodity a
                    LEFT JOIN production_data p ON p.commodity_id = a.id
                    LEFT JOIN planet_class_data pcd ON pcd.commodity_id = a.id
                    WHERE COALESCE(p.production, 0) != 0 OR COALESCE(pcd.count, 0) != 0
                    ORDER BY a.sort ASC',
                    $host->getHostType()->getPlanetFieldHostColumnIdentifier()
                ),
                $rsm
            )
            ->setParameters([
                'state' => 1,
                'hostId' => $host->getId(),
                'colonyClassId' => $colonyClass->getId()
            ])
            ->getResult();
    }

    #[\Override]
    public function getProductionSumForAllUserColonies(User $user): iterable
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('commodity_id', 'commodity_id', 'integer');
        $rsm->addScalarResult('amount', 'amount', 'integer');
        $rsm->addScalarResult('commodity_name', 'commodity_name');

        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT a.id as commodity_id, a.name as commodity_name, SUM(c.count) + COALESCE(MAX(d.count),0) as amount
                FROM stu_commodity a
                    LEFT JOIN stu_colony col ON col.user_id = :userId
                    LEFT JOIN stu_colonies_fielddata b ON b.colonies_id = col.id AND b.aktiv = :state
                    LEFT JOIN stu_buildings_commodity c ON c.commodity_id = a.id AND c.buildings_id = b.buildings_id
                    LEFT JOIN stu_planets_commodity d ON d.commodity_id = a.id AND d.planet_classes_id = col.colonies_classes_id
                WHERE a.type = :commodityType
                GROUP BY a.id
                HAVING SUM(c.count) + COALESCE(MAX(d.count),0) != 0
                ORDER BY a.sort ASC',
                $rsm
            )
            ->setParameters([
                'state' => ColonyFunctionManager::STATE_ENABLED,
                'commodityType' => CommodityTypeConstants::COMMODITY_TYPE_STANDARD,
                'userId' => $user->getId(),
            ])
            ->toIterable();
    }

    #[\Override]
    public function getProductionByCommodityAndUser(int $commodityId, User $user): int
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('gc', 'gc', 'integer');

        return (int) $this->getEntityManager()->createNativeQuery(
            'SELECT SUM(c.count) as gc
            FROM stu_colony d
            LEFT JOIN stu_colonies_fielddata b
                ON b.colonies_id = d.id
                AND b.aktiv = :state
            LEFT JOIN stu_buildings_commodity c
                ON c.buildings_id = b.buildings_id
            LEFT JOIN stu_commodity a
                ON c.commodity_id = a.id
            WHERE d.user_id = :userId
                AND a.id = :commodityId
                AND c.count != 0',
            $rsm
        )->setParameters([
            'state' => 1,
            'userId' => $user->getId(),
            'commodityId' => $commodityId
        ])->getSingleScalarResult();
    }

    #[\Override]
    public function canProduceCommodity(int $userId, int $commodityId): bool
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('buildings_id', 'buildings_id', 'integer');

        $query = $this->getEntityManager()->createNativeQuery(
            'SELECT bc.buildings_id
            FROM stu_buildings_commodity bc
            JOIN stu_buildings b ON bc.buildings_id = b.id
            LEFT JOIN stu_researched r ON b.research_id = r.research_id AND r.user_id = :userId AND r.aktiv = 0
            WHERE bc.commodity_id = :commodityId
            AND bc.count > 0
            AND (b.research_id IS NULL OR r.id IS NOT NULL)',
            $rsm
        );

        $query->setParameters([
            'commodityId' => $commodityId,
            'userId' => $userId
        ]);

        $result = $query->getResult();

        return !empty($result);
    }
}
