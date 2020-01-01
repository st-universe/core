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
            'SELECT id as goods_id,id as global_goods_id,(
                SELECT SUM(a.count) FROM stu_buildings_goods as a LEFT JOIN stu_colonies_fielddata as b USING(buildings_id)
                WHERE a.goods_id = stu_goods.id AND b.colonies_id = :colonyId AND b.aktiv = :state
            ) as gc,(
                SELECT count FROM stu_planets_goods WHERE goods_id = stu_goods.id AND planet_classes_id=:planetTypeId
            ) as pc
                FROM stu_goods GROUP BY id',
            $rsm
        )->setParameters([
            'state' => 1,
            'colonyId' => $colonyId,
            'planetTypeId' => $planetTypeId
        ])->getResult();
    }
}
