<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Component\Anomaly\Type\SubspaceEllipseHandler;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Starmap\Lib\ExploreableStarMap;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\MapInterface;

/**
 * @extends EntityRepository<Map>
 */
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
        int $endCy,
        bool $sortAscending = true
    ): array {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT m FROM %1$s m
                    WHERE m.cx BETWEEN :startCx AND :endCx
                    AND m.cy BETWEEN :startCy AND :endCy
                    AND m.layer_id = :layerId
                    ORDER BY m.cy %2$s, m.cx %2$s',
                    Map::class,
                    $sortAscending ? 'ASC' : 'DESC'
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
        $rsm->addFieldResult('m', 'region_description', 'region_description');
        $rsm->addFieldResult('m', 'layer_id', 'layer_id');

        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT m.id,m.cx,m.cy,m.field_id,m.systems_id,m.bordertype_id,um.user_id,
                    dbu.database_id as mapped, m.influence_area_id as influence_area_id, m.admin_region_id as region_id,
                    sys.name as system_name, m.layer_id,
                    (SELECT tp.id FROM stu_ships s JOIN stu_trade_posts tp ON s.id = tp.ship_id WHERE s.map_id = m.id) as tradepost_id,
                    (SELECT mr.description FROM stu_map_regions mr left JOIN stu_database_user dbu on dbu.user_id = :userId and mr.database_id = dbu.database_id WHERE m.region_id = mr.id) as region_description
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

    public function getForSubspaceEllipseCreation(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('map_id', 'map_id', 'integer');
        $rsm->addScalarResult('descriminator', 'descriminator', 'integer');

        $mapIds = $this->getEntityManager()
            ->createNativeQuery(
                'SELECT map_id, descriminator FROM (
                    SELECT coalesce(sum(r1.tractor_mass) / 10, 0)
                            + coalesce(sum(r2.tractor_mass), 0)
                            + coalesce((SELECT count(ca.id)
                                            FROM stu_crew_assign ca
                                            JOIN stu_ships s
                                            ON ca.ship_id = s.id
                                            WHERE s.user_id >= :firstUserId
                                            AND s.state != :state
                                            AND NOT EXISTS (SELECT ss.id
                                                            FROM stu_ship_system ss
                                                            WHERE ss.ship_id = s.id
                                                            AND ss.system_type = :systemwarp
                                                            AND ss.mode > :mode)
                                            AND s.map_id = m.id)
                                        * (SELECT count(ss.id)
                                            FROM stu_ship_system ss
                                            JOIN stu_ships s
                                            ON ss.ship_id = s.id
                                            WHERE s.user_id >= :firstUserId
                                            AND s.state != :state
                                            AND NOT EXISTS (SELECT ss.id
                                                            FROM stu_ship_system ss
                                                            WHERE ss.ship_id = s.id
                                                            AND ss.system_type = :systemwarp
                                                            AND ss.mode > :mode)
                                            AND s.map_id = m.id
                                            AND ss.mode > :mode)
                                        * 100, 0) - :threshold as descriminator,
                        m.id AS map_id FROM stu_map m
                        JOIN stu_ships s
                        ON s.map_id = m.id
                        LEFT JOIN stu_rumps r1
                        ON s.rumps_id = r1.id
                        and r1.category_id = :rumpCategory
                        LEFT JOIN stu_rumps r2
                        ON s.rumps_id = r2.id
                        AND r2.category_id != :rumpCategory
                        WHERE s.user_id >= :firstUserId
                        AND s.state != :state
                        AND NOT EXISTS (SELECT ss.id
                                        FROM stu_ship_system ss
                                        WHERE ss.ship_id = s.id
                                        AND ss.system_type = :systemwarp
                                        AND ss.mode > :mode)
                        GROUP BY m.id) AS foo
                    WHERE descriminator > 0',
                $rsm
            )
            ->setParameters([
                'threshold' => SubspaceEllipseHandler::MASS_CALCULATION_THRESHOLD,
                'rumpCategory' => ShipRumpEnum::SHIP_CATEGORY_STATION,
                'firstUserId' => UserEnum::USER_FIRST_ID,
                'mode' => ShipSystemModeEnum::MODE_OFF,
                'state' => ShipStateEnum::SHIP_STATE_UNDER_CONSTRUCTION,
                'systemwarp' => ShipSystemTypeEnum::SYSTEM_WARPDRIVE
            ])
            ->getResult();

        $finalIds = [];
        foreach ($mapIds as $entry) {
            $descriminator = $entry['descriminator'];

            if ((int)ceil($descriminator / 1_000_000 + 5) > random_int(1, 100)) {
                $finalIds[] = $entry['map_id'];
            }
        }

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT m FROM %s m
                    WHERE m.id in (:ids)',
                    Map::class
                )
            )
            ->setParameters([
                'ids' => $finalIds
            ])
            ->getResult();
    }

    public function getWithEmptySystem(LayerInterface $layer): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT m from %s m
                    WHERE m.system_type_id IS NOT NULL
                    AND m.systems_id IS NULL
                    AND m.layer = :layer',
                    Map::class
                )
            )
            ->setParameters([
                'layer' => $layer
            ])
            ->getResult();
    }

    public function getRandomMapIdsForAstroMeasurement(int $regionId, int $maxPercentage): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id', 'integer');

        $mapIdResultSet = $this->getEntityManager()
            ->createNativeQuery(
                'SELECT m.id FROM stu_map m
                JOIN stu_map_ftypes mf
                WITH m.field_id = mf.id
                WHERE m.region_id = :regionId
                AND mf.passable IS true',
                $rsm
            )
            ->setParameters([
                'regionId' => $regionId,
            ])
            ->getResult();

        return array_map(fn (array $data) => $data['id'], $mapIdResultSet);
    }
}
