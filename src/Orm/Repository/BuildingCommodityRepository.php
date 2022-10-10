<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

final class BuildingCommodityRepository extends EntityRepository implements BuildingCommodityRepositoryInterface
{
    public function getByBuilding(int $buildingId): array
    {
        return $this->findBy([
            'buildings_id' => $buildingId
        ]);
    }

    public function getProductionByColony(int $colonyId, int $planetTypeId): iterable
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('commodity_id', 'commodity_id', 'integer');
        $rsm->addScalarResult('gc', 'gc', 'integer');
        $rsm->addScalarResult('pc', 'pc', 'integer');

        return $this->getEntityManager()->createNativeQuery(
            'SELECT a.id as commodity_id, SUM(c.count) as gc, COALESCE(MAX(d.count),0) as pc
            FROM stu_goods a
                LEFT JOIN stu_colonies_fielddata b ON b.colonies_id = :colonyId AND b.aktiv = :state
                LEFT JOIN stu_buildings_goods c ON c.goods_id = a.id AND c.buildings_id = b.buildings_id
                LEFT JOIN stu_planets_goods d ON d.goods_id = a.id AND d.planet_classes_id = :planetTypeId
            WHERE c.count != 0 OR d.count != 0
            GROUP BY a.id
            ORDER BY a.sort ASC',
            $rsm
        )->setParameters([
            'state' => 1,
            'colonyId' => $colonyId,
            'planetTypeId' => $planetTypeId
        ])->getResult();
    }

    public function getProductionByCommodityAndUser(int $commodityId, int $userId): int
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('gc', 'gc', 'integer');

        return (int) $this->getEntityManager()->createNativeQuery(
            'SELECT SUM(c.count) as gc
            FROM stu_colonies d
            LEFT JOIN stu_colonies_fielddata b
                ON b.colonies_id = d.id
                AND b.aktiv = :state
            LEFT JOIN stu_buildings_goods c
                ON c.buildings_id = b.buildings_id
            LEFT JOIN stu_goods a
                ON c.goods_id = a.id
            WHERE d.user_id = :userId
                AND a.id = :commodityId
                AND c.count != 0',
            $rsm
        )->setParameters([
            'state' => 1,
            'userId' => $userId,
            'commodityId' => $commodityId
        ])->getSingleScalarResult();
    }
}
