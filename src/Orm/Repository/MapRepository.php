<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Component\Anomaly\Type\SubspaceEllipseHandler;
use Stu\Component\Map\MapEnum;
use Stu\Component\Ship\FlightSignatureVisibilityEnum;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\PlayerSetting\Lib\UserSettingEnum;
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
    public function getAmountByLayer(LayerInterface $layer): int
    {
        return $this->count([
            'layer_id' => $layer->getId()
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

    public function getBorderData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT m.cx AS x, m.cy AS y,
                (SELECT al.rgb_code FROM stu_alliances al
                    JOIN stu_user u ON al.id = u.allys_id
                    JOIN stu_ships s ON u.id = s.user_id
                    JOIN stu_map ma ON ma.influence_area_id = s.influence_area_id
                    WHERE ma.id = m.id AND ma.bordertype_id IS NULL AND ma.admin_region_id IS NULL)
                            AS allycolor,
                (SELECT COALESCE(us.value, \'\') FROM stu_user u
                    LEFT JOIN stu_user_setting us ON u.id = us.user_id
                    JOIN stu_ships s ON u.id = s.user_id
                    JOIN stu_map mu ON mu.influence_area_id = s.influence_area_id
                    WHERE us.setting = :rgbCodeSetting
                    AND mu.id = m.id AND mu.bordertype_id IS NULL AND mu.admin_region_id IS NULL)
                        as usercolor,
                (SELECT mbt.color FROM stu_map_bordertypes mbt
                    JOIN stu_map mb ON mb.bordertype_id = mbt.id
                    WHERE mb.id = m.id AND mb.bordertype_id IS NOT NULL)
                        AS factioncolor
            FROM stu_map m
            WHERE m.cx BETWEEN :xStart AND :xEnd
            AND m.cy BETWEEN :yStart AND :yEnd
            AND m.layer_id = :layerId',
            $rsm
        )->setParameters([
            'xStart' => $boundaries->getMinX(),
            'xEnd' => $boundaries->getMaxX(),
            'yStart' => $boundaries->getMinY(),
            'yEnd' => $boundaries->getMaxY(),
            'layerId' => $boundaries->getParentId(),
            'rgbCodeSetting' => UserSettingEnum::RGB_CODE->value
        ])->getResult();
    }


    public function getMapLayerData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT m.cx as x, m.cy AS y, ft.type
                FROM stu_map m
                JOIN stu_map_ftypes ft ON ft.id = m.field_id
                WHERE m.cx BETWEEN :xStart AND :xEnd AND m.cy BETWEEN :yStart AND :yEnd
                AND m.layer_id = :layerId',
            $rsm
        )->setParameters([
            'xStart' => $boundaries->getMinX(),
            'xEnd' => $boundaries->getMaxX(),
            'yStart' => $boundaries->getMinY(),
            'yEnd' => $boundaries->getMaxY(),
            'layerId' => $boundaries->getParentId(),
        ])->getResult();
    }

    public function getShipCountLayerData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT m.id, m.cx as x, m.cy AS y,
                (SELECT count(DISTINCT b.id) FROM stu_ships b
                    WHERE b.cx = m.cx AND b.cy = m.cy AND b.layer_id = m.layer_id
                    AND NOT EXISTS (SELECT ss.id
                                        FROM stu_ship_system ss
                                        WHERE b.id = ss.ship_id
                                        AND ss.system_type = :cloakSystemId
                                        AND ss.mode > 1)) AS shipcount,
                (SELECT count(DISTINCT c.id) FROM stu_ships c
                    WHERE c.cx = m.cx AND c.cy = m.cy AND c.layer_id = m.layer_id
                    AND EXISTS (SELECT ss2.id
                                        FROM stu_ship_system ss2
                                        WHERE c.id = ss2.ship_id
                                        AND ss2.system_type = :cloakSystemId
                                        AND ss2.mode > 1)) AS cloakcount
            FROM stu_map m
            WHERE m.cx BETWEEN :xStart AND :xEnd AND m.cy BETWEEN :yStart AND :yEnd
            AND m.layer_id = :layerId
            GROUP BY m.id, m.cy, m.cx',
            $rsm
        )->setParameters([
            'xStart' => $boundaries->getMinX(),
            'xEnd' => $boundaries->getMaxX(),
            'yStart' => $boundaries->getMinY(),
            'yEnd' => $boundaries->getMaxY(),
            'layerId' => $boundaries->getParentId(),
            'cloakSystemId' => ShipSystemTypeEnum::SYSTEM_CLOAK->value
        ])->getResult();
    }

    public function getIgnoringSubspaceLayerData(PanelBoundaries $boundaries, int $ignoreId, ResultSetMapping $rsm): array
    {
        $maxAge = time() - FlightSignatureVisibilityEnum::SIG_VISIBILITY_UNCLOAKED;

        return $this->getEntityManager()->createNativeQuery(
            sprintf(
                'SELECT m.cx AS x, m.cy AS y,
                (SELECT count(distinct fs1.ship_id) from stu_flight_sig fs1
                WHERE fs1.map_id = m.id
                AND fs1.user_id != %1$d
                AND (fs1.from_direction = 1 OR fs1.to_direction = 1)
                AND fs1.time > %2$d) as d1c,
                (SELECT count(distinct fs2.ship_id) from stu_flight_sig fs2
                WHERE fs2.map_id = m.id
                AND fs2.user_id != %1$d
                AND (fs2.from_direction = 2 OR fs2.to_direction = 2)
                AND fs2.time > %2$d) as d2c,
                (SELECT count(distinct fs3.ship_id) from stu_flight_sig fs3
                WHERE fs3.map_id = m.id
                AND fs3.user_id != %1$d
                AND (fs3.from_direction = 3 OR fs3.to_direction = 3)
                AND fs3.time > %2$d) as d3c,
                (SELECT count(distinct fs4.ship_id) from stu_flight_sig fs4
                WHERE fs4.map_id = m.id
                AND fs4.user_id != %1$d
                AND (fs4.from_direction = 4 OR fs4.to_direction = 4)
                AND fs4.time > %2$d) as d4c 
                FROM stu_map m
                WHERE m.cx BETWEEN :xStart AND :xEnd
                AND m.cy BETWEEN :yStart AND :yEnd
                AND m.layer_id = :layerId',
                $ignoreId,
                $maxAge
            ),
            $rsm
        )->setParameters([
            'xStart' => $boundaries->getMinX(),
            'xEnd' => $boundaries->getMaxX(),
            'yStart' => $boundaries->getMinY(),
            'yEnd' => $boundaries->getMaxY(),
            'layerId' => $boundaries->getParentId(),
        ])->getResult();
    }

    public function getSignaturesOuterSystem(PanelBoundaries $boundaries, ResultSetMapping $rsm): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT m.id, m.cx as x, m.cy as y, ft.type,
                (SELECT count(distinct b.id)
                    FROM stu_ships b
                    WHERE b.map_id = m.id) as shipcount,
            (SELECT count(distinct fs1.ship_id) from stu_flight_sig fs1
                WHERE fs1.map_id = m.id
                AND (fs1.from_direction = 1 OR fs1.to_direction = 1)) as d1c,
            (SELECT count(distinct fs2.ship_id) from stu_flight_sig fs2
                WHERE fs2.map_id = m.id
                AND (fs2.from_direction = 2 OR fs2.to_direction = 2)) as d2c,
            (SELECT count(distinct fs3.ship_id) from stu_flight_sig fs3
                WHERE fs3.map_id = m.id
                AND (fs3.from_direction = 3 OR fs3.to_direction = 3)) as d3c,
            (SELECT count(distinct fs4.ship_id) from stu_flight_sig fs4
                WHERE fs4.map_id = m.id
                AND (fs4.from_direction = 4 OR fs4.to_direction = 4)) as d4c 
            FROM stu_map m
            LEFT JOIN stu_map_ftypes ft ON ft.id = m.field_id
            WHERE m.cx BETWEEN :xStart AND :xEnd
            AND m.cy BETWEEN :yStart AND :yEnd
            AND m.layer_id = :layerId
            GROUP BY m.cy, m.cx, m.id, ft.type, m.field_id',
            $rsm
        )->setParameters([
            'xStart' => $boundaries->getMinX(),
            'xEnd' => $boundaries->getMaxX(),
            'yStart' => $boundaries->getMinY(),
            'yEnd' => $boundaries->getMaxY(),
            'layerId' => $boundaries->getParentId()
        ])->getResult();
    }

    public function getSignaturesOuterSystemOfUser(PanelBoundaries $boundaries, int $userId, ResultSetMapping $rsm): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT m.id, m.cx as x, m.cy as y, ft.type,
            (SELECT count(distinct b.id)
                FROM stu_ships b
                WHERE b.map_id = m.id
                AND b.user_id = :userId) as shipcount,
            (SELECT count(distinct fs1.ship_id) from stu_flight_sig fs1
                WHERE fs1.map_id = m.id
                AND fs1.user_id = :userId
                AND (fs1.from_direction = 1 OR fs1.to_direction = 1)) as d1c,
            (SELECT count(distinct fs2.ship_id) from stu_flight_sig fs2
                WHERE fs2.map_id = m.id
                AND fs2.user_id = :userId
                AND (fs2.from_direction = 2 OR fs2.to_direction = 2)) as d2c,
            (SELECT count(distinct fs3.ship_id) from stu_flight_sig fs3
                WHERE fs3.map_id = m.id
                AND fs3.user_id = :userId
                AND (fs3.from_direction = 3 OR fs3.to_direction = 3)) as d3c,
            (SELECT count(distinct fs4.ship_id) from stu_flight_sig fs4
                WHERE fs4.map_id = m.id
                AND fs4.user_id = :userId
                AND (fs4.from_direction = 4 OR fs4.to_direction = 4)) as d4c 
            FROM stu_map m
            LEFT JOIN stu_map_ftypes ft ON ft.id = m.field_id
            WHERE m.cx BETWEEN :xStart AND :xEnd
            AND m.cy BETWEEN :yStart AND :yEnd
            AND m.layer_id = :layerId
            GROUP BY m.cy, m.cx, m.id, d.type, m.field_id',
            $rsm
        )->setParameters([
            'xStart' => $boundaries->getMinX(),
            'xEnd' => $boundaries->getMaxX(),
            'yStart' => $boundaries->getMinY(),
            'yEnd' => $boundaries->getMaxY(),
            'layerId' => $boundaries->getParentId(),
            'userId' => $userId
        ])->getResult();
    }

    public function getAllianceSubspaceLayerData(PanelBoundaries $boundaries, int $allianceId, ResultSetMapping $rsm): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT m.id, m.cx as x, m.cy as y, ft.type,
                (SELECT count(distinct b.id)
                    FROM stu_ships b
                    JOIN stu_user u ON b.user_id = u.id
                    WHERE b.map_id = m.id
                    AND u.allys_id = :allyId) as shipcount
                    (SELECT count(distinct fs1.ship_id) from stu_flight_sig fs1
            JOIN stu_user u1 ON fs1.user_id = u1.id
            WHERE fs1.map_id = m.id
            AND u1.allys_id = :allyId
            AND (fs1.from_direction = 1 OR fs1.to_direction = 1)) as d1c,
            (SELECT count(distinct fs2.ship_id) from stu_flight_sig fs2
            JOIN stu_user u2 ON fs2.user_id = u2.id
            WHERE fs2.map_id = m.id
            AND u2.allys_id = :allyId
            AND (fs2.from_direction = 2 OR fs2.to_direction = 2)) as d2c,
            (SELECT count(distinct fs3.ship_id) from stu_flight_sig fs3
            JOIN stu_user u3 ON fs3.user_id = u3.id
            WHERE fs3.map_id = m.id
            AND u3.allys_id = :allyId
            AND (fs3.from_direction = 3 OR fs3.to_direction = 3)) as d3c,
            (SELECT count(distinct fs4.ship_id) from stu_flight_sig fs4
            JOIN stu_user u4 ON fs4.user_id = u4.id
            WHERE fs4.map_id = m.id
            AND u4.allys_id = :allyId
            AND (fs4.from_direction = 4 OR fs4.to_direction = 4)) as d4c 
            FROM stu_map m
            WHERE m.cx BETWEEN :xStart AND :xEnd
            AND m.cy BETWEEN :yStart AND :yEnd
            AND m.layer_id = :layerId',
            $rsm
        )->setParameters([
            'xStart' => $boundaries->getMinX(),
            'xEnd' => $boundaries->getMaxX(),
            'yStart' => $boundaries->getMinY(),
            'yEnd' => $boundaries->getMaxY(),
            'layerId' => $boundaries->getParentId(),
            'allyId' => $allianceId
        ])->getResult();
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
                        m.id AS map_id
                        FROM stu_map m
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
                ON m.field_id = mf.id
                WHERE m.region_id = :regionId
                AND mf.passable IS true
                ORDER BY RANDOM()',
                $rsm
            )
            ->setParameters([
                'regionId' => $regionId,
            ])
            ->getResult();

        $amount = (int)ceil(count($mapIdResultSet) * $maxPercentage / 100);
        $subset = array_slice($mapIdResultSet, 0, $amount);

        return array_map(fn (array $data) => $data['id'], $subset);
    }

    public function getRandomPassableUnoccupiedWithoutDamage(): int
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id', 'integer');

        return (int)$this->getEntityManager()
            ->createNativeQuery(
                'SELECT m.id
                FROM stu_map m
                JOIN stu_map_ftypes mft
                ON m.field_id = mft.id
                WHERE NOT EXISTS (SELECT s.id FROM stu_ships s WHERE s.map_id = m.id)
                AND m.layer_id = :layerId
                AND mft.x_damage = 0
                AND mft.passable = true
                ORDER BY RANDOM()
                LIMIT 1',
                $rsm
            )
            ->setParameter('layerId', MapEnum::DEFAULT_LAYER)
            ->getSingleScalarResult();
    }
}
