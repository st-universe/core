<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

final class BuildingGoodRepository extends EntityRepository implements BuildingGoodRepositoryInterface
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
        $rsm->addScalarResult('goods_id', 'goods_id', 'integer');
        $rsm->addScalarResult('gc', 'gc', 'integer');
        $rsm->addScalarResult('pc', 'pc', 'integer');

        return $this->getEntityManager()->createNativeQuery(
            'SELECT a.id as goods_id, a.id as global_goods_id, SUM(c.count) as gc, MAX(d.count) as pc
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
}
