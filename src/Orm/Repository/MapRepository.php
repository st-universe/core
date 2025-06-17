<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Override;
use RuntimeException;
use Stu\Component\Ship\FlightSignatureVisibilityEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Module\PlayerSetting\Lib\UserSettingEnum;
use Stu\Module\Starmap\Lib\ExploreableStarMap;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends EntityRepository<Map>
 */
final class MapRepository extends EntityRepository implements MapRepositoryInterface
{
    #[Override]
    public function getAmountByLayer(LayerInterface $layer): int
    {
        return $this->count([
            'layer_id' => $layer->getId()
        ]);
    }

    #[Override]
    public function getAllOrdered(int $layerId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT m FROM %s m
                    JOIN %s l
                    WITH m.id = l.id
                    WHERE l.layer_id = :layerId
                    ORDER BY l.cy, l.cx',
                    Map::class,
                    Location::class
                )
            )
            ->setParameters([
                'layerId' => $layerId
            ])
            ->getResult();
    }

    #[Override]
    public function getAllWithSystem(int $layerId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT m FROM %s m INDEX BY m.id
                    JOIN %s l
                    WITH m.id = l.id
                    WHERE l.layer_id = :layerId
                    AND m.systems_id IS NOT null',
                    Map::class,
                    Location::class
                )
            )
            ->setParameters([
                'layerId' => $layerId
            ])
            ->getResult();
    }

    #[Override]
    public function getAllWithoutSystem(int $layerId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT m FROM %s m INDEX BY m.id
                    JOIN %s l
                    WITH m.id = l.id
                    WHERE l.layer_id = :layerId
                    AND m.systems_id IS null',
                    Map::class,
                    Location::class
                )
            )
            ->setParameters([
                'layerId' => $layerId
            ])
            ->getResult();
    }

    #[Override]
    public function getByCoordinates(?LayerInterface $layer, int $cx, int $cy): ?MapInterface
    {
        if ($layer === null) {
            return null;
        }

        return $this->findOneBy([
            'layer_id' => $layer->getId(),
            'cx' => $cx,
            'cy' => $cy
        ]);
    }

    #[Override]
    public function getByBoundaries(PanelBoundaries $boundaries): array
    {
        return $this->getByCoordinateRange(
            $boundaries->getParentId(),
            $boundaries->getMinX(),
            $boundaries->getMaxX(),
            $boundaries->getMinY(),
            $boundaries->getMaxY()
        );
    }

    #[Override]
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
                    'SELECT m FROM %s m
                    JOIN %s l
                    WITH m.id = l.id
                    WHERE l.cx BETWEEN :startCx AND :endCx
                    AND l.cy BETWEEN :startCy AND :endCy
                    AND l.layer_id = :layerId
                    ORDER BY l.cy %3$s, l.cx %3$s',
                    Map::class,
                    Location::class,
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

    #[Override]
    public function save(MapInterface $map): void
    {
        $em = $this->getEntityManager();

        $em->persist($map);
    }

    #[Override]
    public function getNormalBorderData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT l.cx AS x, l.cy AS y
            FROM stu_map m
            JOIN stu_location l
            ON m.id = l.id
            WHERE l.cx BETWEEN :xStart AND :xEnd
            AND l.cy BETWEEN :yStart AND :yEnd
            AND l.layer_id = :layerId',
            $rsm
        )->setParameters([
            'xStart' => $boundaries->getMinX(),
            'xEnd' => $boundaries->getMaxX(),
            'yStart' => $boundaries->getMinY(),
            'yEnd' => $boundaries->getMaxY(),
            'layerId' => $boundaries->getParentId()
        ])->getResult();
    }

    #[Override]
    public function getCartographingData(PanelBoundaries $boundaries, ResultSetMapping $rsm, array $locations): array
    {

        return $this->getEntityManager()->createNativeQuery(
            'SELECT DISTINCT 
                    l.cx AS x, 
                    l.cy AS y, 
                    CASE 
                        WHEN l.id IN (:fieldIds) THEN TRUE ELSE FALSE
                    END AS cartographing,
                    (SELECT mft.complementary_color FROM stu_map_ftypes mft where mft.id = l.field_id) AS complementary_color
                FROM stu_location l
                WHERE l.cx BETWEEN :xStart AND :xEnd
                AND l.cy BETWEEN :yStart AND :yEnd
                AND l.layer_id = :layerId
                ORDER BY cartographing DESC',
            $rsm
        )->setParameters([
            'xStart' => $boundaries->getMinX(),
            'xEnd' => $boundaries->getMaxX(),
            'yStart' => $boundaries->getMinY(),
            'yEnd' => $boundaries->getMaxY(),
            'layerId' => $boundaries->getParentId(),
            'fieldIds' => $locations
        ])->getResult();
    }


    #[Override]
    public function getRegionBorderData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT l.cx AS x, l.cy AS y,
                (SELECT al.rgb_code FROM stu_alliances al
                    JOIN stu_user u ON al.id = u.allys_id
                    JOIN stu_spacecraft sc ON u.id = sc.user_id
                    JOIN stu_station s ON sc.id = s.id
                    JOIN stu_map ma ON ma.influence_area_id = s.influence_area_id
                    WHERE ma.id = m.id AND ma.bordertype_id IS NULL AND ma.admin_region_id IS NULL)
                            AS allycolor,
                (SELECT COALESCE(us.value, \'\') FROM stu_user u
                    LEFT JOIN stu_user_setting us ON u.id = us.user_id
                    JOIN stu_spacecraft sc ON u.id = sc.user_id
                    JOIN stu_station s ON sc.id = s.id
                    JOIN stu_map mu ON mu.influence_area_id = s.influence_area_id
                    WHERE us.setting = :rgbCodeSetting
                    AND mu.id = m.id AND mu.bordertype_id IS NULL AND mu.admin_region_id IS NULL)
                        as usercolor,
                (SELECT mbt.color FROM stu_map_bordertypes mbt
                    JOIN stu_map mb ON mb.bordertype_id = mbt.id
                    WHERE mb.id = m.id AND mb.bordertype_id IS NOT NULL)
                        AS factioncolor
            FROM stu_map m
            JOIN stu_location l
            ON m.id = l.id
            WHERE l.cx BETWEEN :xStart AND :xEnd
            AND l.cy BETWEEN :yStart AND :yEnd
            AND l.layer_id = :layerId',
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

    #[Override]
    public function getImpassableBorderData(PanelBoundaries $boundaries, Userinterface $user, ResultSetMapping $rsm): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT DISTINCT 
                l.cx AS x, 
                l.cy AS y,
                CASE 
                    WHEN mf.passable = FALSE 
                         AND EXISTS (
                            SELECT 1 
                            FROM stu_database_user du
                            JOIN stu_database_entrys de ON du.database_id = de.id
                            WHERE du.user_id = :userId
                            AND de.id = r.database_id
                         ) 
                    THEN FALSE
                    ELSE TRUE
                END AS impassable,
                    (SELECT mft.complementary_color FROM stu_map_ftypes mft where mft.id = l.field_id) AS complementary_color
            FROM stu_location l
            LEFT JOIN stu_map_ftypes mf ON l.field_id = mf.id
            LEFT JOIN stu_map m ON l.id = m.id
            LEFT JOIN stu_map_regions r ON m.region_id = r.id
            WHERE l.cx BETWEEN :xStart AND :xEnd
              AND l.cy BETWEEN :yStart AND :yEnd
              AND l.layer_id = :layerId',
            $rsm
        )->setParameters([
            'xStart' => $boundaries->getMinX(),
            'xEnd' => $boundaries->getMaxX(),
            'yStart' => $boundaries->getMinY(),
            'yEnd' => $boundaries->getMaxY(),
            'layerId' => $boundaries->getParentId(),
            'userId' => $user->getId()
        ])->getResult();
    }


    #[Override]
    public function getAnomalyData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT l.cx AS x, l.cy AS y,
                (SELECT array_to_string(array(SELECT a.anomaly_type_id FROM stu_anomaly a WHERE a.location_id = m.id), \',\')) as anomalytypes
            FROM stu_map m
            JOIN stu_location l
            ON m.id = l.id
            WHERE l.cx BETWEEN :xStart AND :xEnd
            AND l.cy BETWEEN :yStart AND :yEnd
            AND l.layer_id = :layerId',
            $rsm
        )->setParameters([
            'xStart' => $boundaries->getMinX(),
            'xEnd' => $boundaries->getMaxX(),
            'yStart' => $boundaries->getMinY(),
            'yEnd' => $boundaries->getMaxY(),
            'layerId' => $boundaries->getParentId(),
        ])->getResult();
    }

    #[Override]
    public function getSpacecraftCountLayerData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT l.cx as x, l.cy AS y, mft.effects as effects,
                (SELECT count(DISTINCT b.id) FROM stu_spacecraft b
                    JOIN stu_location l2
                    ON b.location_id = l2.id
                    WHERE l2.layer_id = l.layer_id 
                    AND l2.cx = l.cx
                    AND l2.cy = l.cy
                    AND NOT EXISTS (SELECT ss.id
                                        FROM stu_spacecraft_system ss
                                        WHERE b.id = ss.spacecraft_id
                                        AND ss.system_type = :cloakSystemId
                                        AND ss.mode > 1)) AS spacecraftcount,
                (SELECT count(DISTINCT c.id) FROM stu_spacecraft c
                    JOIN stu_location l2
                    ON c.location_id = l2.id
                    WHERE l2.layer_id = l.layer_id 
                    AND l2.cx = l.cx
                    AND l2.cy = l.cy
                    AND EXISTS (SELECT ss2.id
                                        FROM stu_spacecraft_system ss2
                                        WHERE c.id = ss2.spacecraft_id
                                        AND ss2.system_type = :cloakSystemId
                                        AND ss2.mode > 1)) AS cloakcount
            FROM stu_map m
            JOIN stu_location l
            ON m.id = l.id
            JOIN stu_map_ftypes mft
            ON l.field_id = mft.id
            WHERE l.cx BETWEEN :xStart AND :xEnd
            AND l.cy BETWEEN :yStart AND :yEnd
            AND l.layer_id = :layerId',
            $rsm
        )->setParameters([
            'xStart' => $boundaries->getMinX(),
            'xEnd' => $boundaries->getMaxX(),
            'yStart' => $boundaries->getMinY(),
            'yEnd' => $boundaries->getMaxY(),
            'layerId' => $boundaries->getParentId(),
            'cloakSystemId' => SpacecraftSystemTypeEnum::CLOAK->value
        ])->getResult();
    }


    #[Override]
    public function getMapLayerData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT l.cx as x, l.cy AS y, ft.type
                FROM stu_map m
                JOIN stu_location l
                ON m.id = l.id
                JOIN stu_map_ftypes ft ON ft.id = l.field_id
                WHERE l.cx BETWEEN :xStart AND :xEnd AND l.cy BETWEEN :yStart AND :yEnd
                AND l.layer_id = :layerId',
            $rsm
        )->setParameters([
            'xStart' => $boundaries->getMinX(),
            'xEnd' => $boundaries->getMaxX(),
            'yStart' => $boundaries->getMinY(),
            'yEnd' => $boundaries->getMaxY(),
            'layerId' => $boundaries->getParentId(),
        ])->getResult();
    }

    #[Override]
    public function getUserSpacecraftCountLayerData(PanelBoundaries $boundaries, int $userId, ResultSetMapping $rsm): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT l.cx as x, l.cy as y,
            (SELECT count(distinct s.id)
                FROM stu_spacecraft s
                JOIN stu_location spl
                ON s.location_id = spl.id
                WHERE spl.cx = l.cx
                AND spl.cy = l.cy
                AND spl.layer_id = l.layer_id
                AND s.user_id = :userId) as spacecraftcount
            FROM stu_map m
            JOIN stu_location l
            ON m.id = l.id
            WHERE l.cx BETWEEN :xStart AND :xEnd
            AND l.cy BETWEEN :yStart AND :yEnd
            AND l.layer_id = :layerId',
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

    #[Override]
    public function getAllianceSpacecraftCountLayerData(PanelBoundaries $boundaries, int $allianceId, ResultSetMapping $rsm): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT l.cx as x, l.cy as y,
             (SELECT count(distinct s.id)
                    FROM stu_spacecraft s
                    JOIN stu_location spl
                    ON s.location_id = spl.id
                    JOIN stu_user u
                    ON s.user_id = u.id
                    WHERE spl.cx = l.cx
                    AND spl.cy = l.cy
                    AND spl.layer_id = l.layer_id
                    AND u.allys_id = :allyId) as spacecraftcount
            FROM stu_map m
            JOIN stu_location l
            ON m.id = l.id
            WHERE l.cx BETWEEN :xStart AND :xEnd
            AND l.cy BETWEEN :yStart AND :yEnd
            AND l.layer_id = :layerId',
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

    #[Override]
    public function getSpacecraftCountLayerDataForSpacecraft(PanelBoundaries $boundaries, int $spacecraftId, ResultSetMapping $rsm): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT l.cx as x, l.cy as y,
            (SELECT count(distinct s.id)
                FROM stu_spacecraft s
                JOIN stu_location spl
                ON s.location_id = spl.id
                WHERE spl.cx = l.cx
                AND spl.cy = l.cy
                AND spl.layer_id = l.layer_id
                AND s.id = :spacecraftId) as spacecraftcount
            FROM stu_map m
            JOIN stu_location l
            ON m.id = l.id
            WHERE l.cx BETWEEN :xStart AND :xEnd
            AND l.cy BETWEEN :yStart AND :yEnd
            AND l.layer_id = :layerId',
            $rsm
        )->setParameters([
            'xStart' => $boundaries->getMinX(),
            'xEnd' => $boundaries->getMaxX(),
            'yStart' => $boundaries->getMinY(),
            'yEnd' => $boundaries->getMaxY(),
            'layerId' => $boundaries->getParentId(),
            'spacecraftId' => $spacecraftId
        ])->getResult();
    }

    #[Override]
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
                'SELECT m.id, l.cx, l.cy, l.field_id, m.systems_id, m.bordertype_id, um.user_id,
                    dbu.database_id as mapped, m.influence_area_id as influence_area_id, m.admin_region_id as region_id,
                    sys.name as system_name, l.layer_id,
                    (SELECT tp.id FROM stu_spacecraft s JOIN stu_trade_posts tp ON s.id = tp.station_id WHERE s.location_id = m.id) as tradepost_id,
                    (SELECT mr.description FROM stu_map_regions mr JOIN stu_database_user dbu on dbu.user_id = :userId and mr.database_id = dbu.database_id WHERE m.region_id = mr.id) as region_description
                FROM stu_map m
                JOIN stu_location l
                ON m.id = l.id
                LEFT JOIN stu_user_map um
                    ON um.cy = l.cy AND um.cx = l.cx AND um.user_id = :userId AND um.layer_id = l.layer_id
                LEFT JOIN stu_systems sys
                    ON m.systems_id = sys.id
                LEFT JOIN stu_database_user dbu
                    ON dbu.user_id = :userId
                    AND sys.database_id = dbu.database_id
                WHERE l.cx BETWEEN :startX AND :endX
                AND l.cy = :cy
                AND l.layer_id = :layerId
                ORDER BY l.cx ASC',
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

    #[Override]
    public function getWithEmptySystem(LayerInterface $layer): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT m from %s m
                    JOIN %s l
                    WITH m.id = l.id
                    WHERE m.system_type_id IS NOT NULL
                    AND m.systems_id IS NULL
                    AND l.layer = :layer',
                    Map::class,
                    Location::class
                )
            )
            ->setParameters([
                'layer' => $layer
            ])
            ->getResult();
    }

    #[Override]
    public function getRandomMapIdsForAstroMeasurement(int $regionId, int $maxPercentage, int $location): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id', 'integer');

        $mapIdResultSet = $this->getEntityManager()
            ->createNativeQuery(
                "SELECT m.id
                FROM stu_map m
                JOIN stu_location l ON m.id = l.id
                JOIN stu_map_ftypes mf ON l.field_id = mf.id
                WHERE m.region_id = :regionId
                AND 
                    mf.passable = :true
                    AND NOT EXISTS (
                        SELECT 1
                        FROM jsonb_array_elements_text(mf.effects::jsonb) AS elem
                        WHERE elem = 'NO_MEASUREPOINT'
                    
                )
                AND m.id != :loc
                ORDER BY RANDOM()",
                $rsm
            )
            ->setParameters([
                'regionId' => $regionId,
                'loc' => $location,
                'true' => true
            ])
            ->getResult();

        $amount = (int)ceil(count($mapIdResultSet) * $maxPercentage / 100);
        $subset = array_slice($mapIdResultSet, 0, $amount);

        return array_map(fn(array $data) => $data['id'], $subset);
    }

    #[Override]
    public function getRandomPassableUnoccupiedWithoutDamage(LayerInterface $layer, bool $isAtBorder = false): MapInterface
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id', 'integer');

        $borderCriteria = $isAtBorder ?
            sprintf(
                'AND (l.cx in (1, %d) OR l.cy in (1, %d))',
                $layer->getWidth(),
                $layer->getHeight()
            ) : '';

        $randomMapId =  (int)$this->getEntityManager()
            ->createNativeQuery(
                sprintf(
                    'SELECT m.id
                    FROM stu_map m
                    JOIN stu_location l
                    ON m.id = l.id
                    JOIN stu_map_ftypes mft
                    ON l.field_id = mft.id
                    WHERE NOT EXISTS (SELECT s.id FROM stu_spacecraft s WHERE s.location_id = m.id)
                    AND l.layer_id = :layerId
                    AND mft.x_damage = 0
                    AND mft.passable = :true
                    %s
                    ORDER BY RANDOM()
                    LIMIT 1',
                    $borderCriteria
                ),
                $rsm
            )
            ->setParameters([
                'layerId' => $layer->getId(),
                'true' => true
            ])
            ->getSingleScalarResult();

        $map = $this->find($randomMapId);
        if ($map === null) {
            throw new RuntimeException('this should not happen');
        }

        return $map;
    }

    #[Override]
    public function getIgnoringSubspaceLayerData(PanelBoundaries $boundaries, int $ignoreUserId, ResultSetMapping $rsm): array
    {
        $maxAge = time() - FlightSignatureVisibilityEnum::SIG_VISIBILITY_UNCLOAKED;

        return $this->getEntityManager()->createNativeQuery(
            'SELECT l.cx AS x, l.cy AS y, mft.effects as effects,
                (SELECT count(distinct fs1.ship_id) from stu_flight_sig fs1
                WHERE fs1.location_id = l.id
                AND fs1.user_id != :ignoreUserId
                AND (fs1.from_direction = 1 OR fs1.to_direction = 1)
                AND fs1.time > :timeThreshold) as d1c,
                (SELECT count(distinct fs2.ship_id) from stu_flight_sig fs2
                WHERE fs2.location_id = l.id
                AND fs2.user_id !=:ignoreUserId
                AND (fs2.from_direction = 2 OR fs2.to_direction = 2)
                AND fs2.time > :timeThreshold) as d2c,
                (SELECT count(distinct fs3.ship_id) from stu_flight_sig fs3
                WHERE fs3.location_id = l.id
                AND fs3.user_id != :ignoreUserId
                AND (fs3.from_direction = 3 OR fs3.to_direction = 3)
                AND fs3.time > :timeThreshold) as d3c,
                (SELECT count(distinct fs4.ship_id) from stu_flight_sig fs4
                WHERE fs4.location_id = l.id
                AND fs4.user_id != :ignoreUserId
                AND (fs4.from_direction = 4 OR fs4.to_direction = 4)
                AND fs4.time > :timeThreshold) as d4c 
                FROM stu_location l
                JOIN stu_map m
                ON l.id = m.id
                JOIN stu_map_ftypes mft
                ON l.field_id = mft.id
                WHERE l.cx BETWEEN :xStart AND :xEnd
                AND l.cy BETWEEN :yStart AND :yEnd
                AND l.layer_id = :layerId',
            $rsm
        )->setParameters([
            'xStart' => $boundaries->getMinX(),
            'xEnd' => $boundaries->getMaxX(),
            'yStart' => $boundaries->getMinY(),
            'yEnd' => $boundaries->getMaxY(),
            'layerId' => $boundaries->getParentId(),
            'ignoreUserId' => $ignoreUserId,
            'timeThreshold' => $maxAge
        ])->getResult();
    }

    #[Override]
    public function getSubspaceLayerData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT l.cx as x, l.cy as y, mft.effects as effects,
            (SELECT count(distinct fs1.ship_id) from stu_flight_sig fs1
                WHERE fs1.location_id = l.id
                AND (fs1.from_direction = 1 OR fs1.to_direction = 1)) as d1c,
            (SELECT count(distinct fs2.ship_id) from stu_flight_sig fs2
                WHERE fs2.location_id = l.id
                AND (fs2.from_direction = 2 OR fs2.to_direction = 2)) as d2c,
            (SELECT count(distinct fs3.ship_id) from stu_flight_sig fs3
                WHERE fs3.location_id = l.id
                AND (fs3.from_direction = 3 OR fs3.to_direction = 3)) as d3c,
            (SELECT count(distinct fs4.ship_id) from stu_flight_sig fs4
                WHERE fs4.location_id = l.id
                AND (fs4.from_direction = 4 OR fs4.to_direction = 4)) as d4c 
            FROM stu_location l
            JOIN stu_map_ftypes mft
            ON l.field_id = mft.id
            WHERE l.cx BETWEEN :xStart AND :xEnd
            AND l.cy BETWEEN :yStart AND :yEnd
            AND l.layer_id = :layerId',
            $rsm
        )->setParameters([
            'xStart' => $boundaries->getMinX(),
            'xEnd' => $boundaries->getMaxX(),
            'yStart' => $boundaries->getMinY(),
            'yEnd' => $boundaries->getMaxY(),
            'layerId' => $boundaries->getParentId()
        ])->getResult();
    }

    #[Override]
    public function getUserSubspaceLayerData(PanelBoundaries $boundaries, int $userId, ResultSetMapping $rsm): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT l.cx as x, l.cy as y,
            (SELECT count(distinct fs1.ship_id) from stu_flight_sig fs1
                WHERE fs1.location_id = l.id
                AND fs1.user_id = :userId
                AND (fs1.from_direction = 1 OR fs1.to_direction = 1)) as d1c,
            (SELECT count(distinct fs2.ship_id) from stu_flight_sig fs2
                WHERE fs2.location_id = l.id
                AND fs2.user_id = :userId
                AND (fs2.from_direction = 2 OR fs2.to_direction = 2)) as d2c,
            (SELECT count(distinct fs3.ship_id) from stu_flight_sig fs3
                WHERE fs3.location_id = l.id
                AND fs3.user_id = :userId
                AND (fs3.from_direction = 3 OR fs3.to_direction = 3)) as d3c,
            (SELECT count(distinct fs4.ship_id) from stu_flight_sig fs4
                WHERE fs4.location_id = l.id
                AND fs4.user_id = :userId
                AND (fs4.from_direction = 4 OR fs4.to_direction = 4)) as d4c 
            FROM stu_location l
            WHERE l.cx BETWEEN :xStart AND :xEnd
            AND l.cy BETWEEN :yStart AND :yEnd
            AND l.layer_id = :layerId',
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

    #[Override]
    public function getShipSubspaceLayerData(PanelBoundaries $boundaries, int $shipId, ResultSetMapping $rsm): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT l.cx as x, l.cy as y,
            (SELECT count(distinct fs1.ship_id) from stu_flight_sig fs1
                WHERE fs1.location_id = l.id
                AND fs1.ship_id = :shipId
                AND (fs1.from_direction = 1 OR fs1.to_direction = 1)) as d1c,
            (SELECT count(distinct fs2.ship_id) from stu_flight_sig fs2
                WHERE fs2.location_id = l.id
                AND fs2.ship_id = :shipId
                AND (fs2.from_direction = 2 OR fs2.to_direction = 2)) as d2c,
            (SELECT count(distinct fs3.ship_id) from stu_flight_sig fs3
                WHERE fs3.location_id = l.id
                AND fs3.ship_id = :shipId
                AND (fs3.from_direction = 3 OR fs3.to_direction = 3)) as d3c,
            (SELECT count(distinct fs4.ship_id) from stu_flight_sig fs4
                WHERE fs4.location_id = l.id
                AND fs4.ship_id = :shipId
                AND (fs4.from_direction = 4 OR fs4.to_direction = 4)) as d4c 
            FROM stu_location l
            WHERE l.cx BETWEEN :xStart AND :xEnd
            AND l.cy BETWEEN :yStart AND :yEnd
            AND l.layer_id = :layerId',
            $rsm
        )->setParameters([
            'xStart' => $boundaries->getMinX(),
            'xEnd' => $boundaries->getMaxX(),
            'yStart' => $boundaries->getMinY(),
            'yEnd' => $boundaries->getMaxY(),
            'layerId' => $boundaries->getParentId(),
            'shipId' => $shipId
        ])->getResult();
    }

    #[Override]
    public function getAllianceSubspaceLayerData(PanelBoundaries $boundaries, int $allianceId, ResultSetMapping $rsm): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT l.id, l.cx as x, l.cy as y,
            (SELECT count(distinct fs1.ship_id) from stu_flight_sig fs1
                JOIN stu_user u1 ON fs1.user_id = u1.id
                WHERE fs1.location_id = l.id
                AND u1.allys_id = :allyId
                AND (fs1.from_direction = 1 OR fs1.to_direction = 1)) as d1c,
            (SELECT count(distinct fs2.ship_id) from stu_flight_sig fs2
                JOIN stu_user u2 ON fs2.user_id = u2.id
                WHERE fs2.location_id = l.id
                AND u2.allys_id = :allyId
                AND (fs2.from_direction = 2 OR fs2.to_direction = 2)) as d2c,
            (SELECT count(distinct fs3.ship_id) from stu_flight_sig fs3
                JOIN stu_user u3 ON fs3.user_id = u3.id
                WHERE fs3.location_id = l.id
                AND u3.allys_id = :allyId
                AND (fs3.from_direction = 3 OR fs3.to_direction = 3)) as d3c,
            (SELECT count(distinct fs4.ship_id) from stu_flight_sig fs4
                JOIN stu_user u4 ON fs4.user_id = u4.id
                WHERE fs4.location_id = l.id
                AND u4.allys_id = :allyId
                AND (fs4.from_direction = 4 OR fs4.to_direction = 4)) as d4c 
            FROM stu_location l
            WHERE l.cx BETWEEN :xStart AND :xEnd
            AND l.cy BETWEEN :yStart AND :yEnd
            AND l.layer_id = :layerId',
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

    public function getUniqueInfluenceAreaIds(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('influence_area_id', 'influence_area_id', 'integer');

        $query = $this->getEntityManager()->createNativeQuery(
            'SELECT DISTINCT influence_area_id
             FROM stu_map
             WHERE influence_area_id IS NOT NULL
             ORDER BY influence_area_id ASC',
            $rsm
        );

        return array_map('intval', array_column($query->getResult(), 'influence_area_id'));
    }
}
