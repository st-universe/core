<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\ShipStorage;
use Stu\Orm\Entity\ShipStorageInterface;

final class ShipStorageRepository extends EntityRepository implements ShipStorageRepositoryInterface
{
    public function prototype(): ShipStorageInterface
    {
        return new ShipStorage();
    }

    public function save(ShipStorageInterface $shipStorage): void
    {
        $em = $this->getEntityManager();

        $em->persist($shipStorage);
    }

    public function delete(ShipStorageInterface $shipStorage): void
    {
        $em = $this->getEntityManager();

        $em->remove($shipStorage);
    }

    public function getByUserAndCommodity(int $userId, int $commodityId): iterable
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('commodity_id', 'commodity_id', 'integer');
        $rsm->addScalarResult('ships_id', 'ships_id', 'integer');
        $rsm->addScalarResult('amount', 'amount', 'integer');

        return $this->getEntityManager()->createNativeQuery(
            'SELECT ss.goods_id AS commodity_id, ss.ships_id AS ships_id, ss.count AS amount
            FROM stu_ships_storage ss
            LEFT JOIN stu_goods g ON g.id = ss.goods_id
            LEFT JOIN stu_ships s ON ss.ships_id = s.id
            WHERE s.user_id = :userId
            AND g.id = :commodityId
            ORDER BY ss.count DESC',
            $rsm
        )->setParameters([
            'userId' => $userId,
            'commodityId' => $commodityId
        ])->getResult();
    }
}
