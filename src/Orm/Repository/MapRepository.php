<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Module\Starmap\Lib\ExploreableStarMap;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\MapInterface;

final class MapRepository extends EntityRepository implements MapRepositoryInterface
{
    public function getAmountByLayer(int $layerId): int
    {
        return $this->count([
            'layer_id' => $layerId
        ]);
    }

    public function getAllOrdered(int $layerId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT m FROM %s m
                    JOIN %s l
                    WITH m.layer_id = l.id
                    WHERE m.cx BETWEEN 1 AND l.width
                    AND m.cy BETWEEN 1 AND l.height
                    AND m.layer_id = :layerId
                    ORDER BY m.cy, m.cx',
                    Map::class,
                    Layer::class
                )
            )
            ->setParameters([
                'layerId' => $layerId
            ])
            ->getResult();
    }

    public function getAllWithSystem(int $layerId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT m FROM %s m INDEX BY m.id
                    JOIN %s l
                    WITH m.layer_id = l.id
                    WHERE m.cx BETWEEN 1 AND l.width
                    AND m.cy BETWEEN 1 AND l.height
                    AND m.layer_id = :layerId
                    AND m.systems_id IS NOT null',
                    Map::class,
                    Layer::class
                )
            )
            ->setParameters([
                'layerId' => $layerId
            ])
            ->getResult();
    }

    public function getAllWithoutSystem(int $layerId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT m FROM %s m INDEX BY m.id
                    JOIN %s l
                    WITH m.layer_id = l.id
                    WHERE m.cx BETWEEN 1 AND l.width
                    AND m.cy BETWEEN 1 AND l.height
                    AND m.layer_id = :layerId
                    AND m.systems_id IS null',
                    Map::class,
                    Layer::class
                )
            )
            ->setParameters([
                'layerId' => $layerId
            ])
            ->getResult();
    }

    public function getByCoordinates(int $layerId, int $cx, int $cy): ?MapInterface
    {
        return $this->findOneBy([
            'layer_id' => $layerId,
            'cx' => $cx,
            'cy' => $cy
        ]);
    }

    public function getByCoordinateRange(
        int $layerId,
        int $startCx,
        int $endCx,
        int $startCy,
        int $endCy
    ): array {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT m FROM %s m
                    WHERE m.cx BETWEEN :startCx AND :endCx
                    AND m.cy BETWEEN :startCy AND :endCy
                    AND m.layer_id = :layerId
                    ORDER BY m.cy, m.cx',
                    Map::class
                )
            )
            ->setParameters([
                'layerId' => $layerId,
                'startCx' => $startCx,
                'endCx' => $endCx,
                'startCy' => $startCy,
                'endCy' => $endCy
            ])
            ->getResult();
    }

    public function save(MapInterface $map): void
    {
        $em = $this->getEntityManager();

        $em->persist($map);
    }

    public function getExplored(int $userId, int $layerId, int $startX, int $endX, int $cy): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(ExploreableStarMap::class, 'm');
        $rsm->addFieldResult('m', 'id', 'id');
        $rsm->addFieldResult('m', 'cx', 'cx');
        $rsm->addFieldResult('m', 'cy', 'cy');
        $rsm->addFieldResult('m', 'field_id', 'field_id');
        $rsm->addFieldResult('m', 'bordertype_id', 'bordertype_id');
        $rsm->addFieldResult('m', 'user_id', 'user_id');
        $rsm->addFieldResult('m', 'mapped', 'mapped');
        $rsm->addFieldResult('m', 'system_name', 'system_name');
        $rsm->addFieldResult('m', 'influence_area_id', 'influence_area_id');
        $rsm->addFieldResult('m', 'region_id', 'region_id');
        $rsm->addFieldResult('m', 'tradepost_id', 'tradepost_id');

        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT m.id,m.cx,m.cy,m.field_id,m.systems_id,m.bordertype_id,um.user_id,
                    dbu.database_id as mapped, m.influence_area_id as influence_area_id, m.admin_region_id as region_id,
                    sys.name as system_name,
                    (SELECT tp.id FROM stu_ships s JOIN stu_trade_posts tp ON s.id = tp.ship_id WHERE s.map_id = m.id) as tradepost_id
                FROM stu_map m
                LEFT JOIN stu_user_map um
                    ON um.cy = m.cy AND um.cx = m.cx AND um.user_id = :userId AND um.layer_id = m.layer_id
                LEFT JOIN stu_systems sys
                    ON m.systems_id = sys.id
                LEFT JOIN stu_database_user dbu
                    ON dbu.user_id = :userId
                    AND sys.database_id = dbu.database_id
                WHERE m.cx BETWEEN :startX AND :endX
                AND m.cy = :cy
                AND m.layer_id = :layerId
                ORDER BY m.cx ASC',
                $rsm
            )
            ->setParameters([
                'layerId' => $layerId,
                'userId' => $userId,
                'startX' => $startX,
                'endX' => $endX,
                'cy' => $cy
            ])
            ->getResult();
    }

    public function getRandomPassableUnoccupiedWithoutDamage(): int
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id', 'integer');

        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT m.id
                FROM stu_map m
                JOIN stu_layer l
                ON m.layer_id = l.id
                JOIN stu_map_ftypes mft
                ON m.field_id = mft.id
                WHERE NOT EXISTS (SELECT s.id FROM stu_ships s WHERE s.map_id = m.id)
                AND l.is_hidden = false
                AND mft.x_damage = 0
                AND mft.passable = true
                ORDER BY RANDOM()
                LIMIT 1',
                $rsm
            )
            ->getSingleScalarResult();
    }
}
