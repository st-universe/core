<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Component\Colony\ColonyFunctionManager;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Orm\Entity\BuildingCommodity;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends EntityRepository<BuildingCommodity>
 */
final class BuildingCommodityRepository extends EntityRepository implements BuildingCommodityRepositoryInterface
{
    public function getByBuilding(int $buildingId): array
    {
        return $this->findBy([
            'buildings_id' => $buildingId
        ]);
    }

    public function getProductionByColony(int $colonyId, int $colonyClassId): iterable
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('commodity_id', 'commodity_id', 'integer');
        $rsm->addScalarResult('production', 'production', 'integer');
        $rsm->addScalarResult('pc', 'pc', 'integer');

        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT a.id as commodity_id, COALESCE(SUM(c.count), 0) as production, COALESCE(MAX(d.count),0) as pc
                FROM stu_commodity a
                    LEFT JOIN stu_colonies_fielddata b ON b.colonies_id = :colonyId AND b.aktiv = :state
                    LEFT JOIN stu_buildings_commodity c ON c.commodity_id = a.id AND c.buildings_id = b.buildings_id
                    LEFT JOIN stu_planets_commodity d ON d.commodity_id = a.id AND d.planet_classes_id = :colonyClassId
                WHERE c.count != 0 OR d.count != 0
                GROUP BY a.id
                ORDER BY a.sort ASC',
                $rsm
            )
            ->setParameters([
                'state' => 1,
                'colonyId' => $colonyId,
                'colonyClassId' => $colonyClassId
            ])
            ->getResult();
    }

    public function getProductionSumForAllUserColonies(UserInterface $user): iterable
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('commodity_id', 'commodity_id', 'integer');
        $rsm->addScalarResult('amount', 'amount', 'integer');
        $rsm->addScalarResult('commodity_name', 'commodity_name');

        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT a.id as commodity_id, a.name as commodity_name, SUM(c.count) + COALESCE(MAX(d.count),0) as amount
                FROM stu_commodity a
                    LEFT JOIN stu_colonies col ON col.user_id = :userId
                    LEFT JOIN stu_colonies_fielddata b ON b.colonies_id = col.id AND b.aktiv = :state
                    LEFT JOIN stu_buildings_commodity c ON c.commodity_id = a.id AND c.buildings_id = b.buildings_id
                    LEFT JOIN stu_planets_commodity d ON d.commodity_id = a.id AND d.planet_classes_id = col.colonies_classes_id
                WHERE a.type = :commodityType
                GROUP BY a.id
                HAVING SUM(c.count) + COALESCE(MAX(d.count),0) > 0
                ORDER BY a.sort ASC',
                $rsm
            )
            ->setParameters([
                'state' => ColonyFunctionManager::STATE_ENABLED,
                'commodityType' => CommodityTypeEnum::COMMODITY_TYPE_STANDARD,
                'userId' => $user->getId(),
            ])
            ->toIterable();
    }

    public function getProductionByCommodityAndUser(int $commodityId, UserInterface $user): int
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('gc', 'gc', 'integer');

        return (int) $this->getEntityManager()->createNativeQuery(
            'SELECT SUM(c.count) as gc
            FROM stu_colonies d
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
}
