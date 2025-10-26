<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Ship\FlightSignatureVisibilityEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\AstronomicalEntry;
use Stu\Orm\Entity\StarSystem;
use Stu\Orm\Entity\StarSystemMap;

/**
 * @extends EntityRepository<StarSystemMap>
 */
final class StarSystemMapRepository extends EntityRepository implements StarSystemMapRepositoryInterface
{
    public function getBySystemOrdered(int $starSystemId): array
    {
        return $this->findBy(
            ['systems_id' => $starSystemId],
            ['sy' => 'asc', 'sx' => 'asc']
        );
    }

    #[\Override]
    public function getByCoordinates(
        int $starSystemId,
        int $sx,
        int $sy
    ): ?StarSystemMap {
        return $this->findOneBy([
            'systems_id' => $starSystemId,
            'sx' => $sx,
            'sy' => $sy
        ]);
    }

    #[\Override]
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

    #[\Override]
    public function getByCoordinateRange(
        int $starSystemId,
        int $startSx,
        int $endSx,
        int $startSy,
        int $endSy,
        bool $sortAscending = true
    ): array {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT m FROM %1$s m
                    WHERE m.systems_id = :starSystemId AND
                        m.sx BETWEEN :startSx AND :endSx AND
                        m.sy BETWEEN :startSy AND :endSy
                    ORDER BY m.sy %2$s, m.sx %2$s',
                    StarSystemMap::class,
                    $sortAscending ? 'ASC' : 'DESC'
                )
            )
            ->setParameters([
                'starSystemId' => $starSystemId,
                'startSx' => $startSx,
                'endSx' => $endSx,
                'startSy' => $startSy,
                'endSy' => $endSy
            ])
            ->getResult();
    }

    #[\Override]
    public function getMapLayerData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT sm.sx as x, sm.sy AS y, ft.type
                FROM stu_sys_map sm
                JOIN stu_location l
                ON sm.id = l.id
                JOIN stu_map_ftypes ft ON ft.id = l.field_id
                WHERE sm.sx BETWEEN :xStart AND :xEnd AND sm.sy BETWEEN :yStart AND :yEnd
                AND sm.systems_id = :systemId',
            $rsm
        )->setParameters([
            'xStart' => $boundaries->getMinX(),
            'xEnd' => $boundaries->getMaxX(),
            'yStart' => $boundaries->getMinY(),
            'yEnd' => $boundaries->getMaxY(),
            'systemId' => $boundaries->getParentId()
        ])->getResult();
    }

    #[\Override]
    public function getSpacecraftCountLayerData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT sm.id, sm.sx as x, sm.sy AS y,
                (SELECT count(DISTINCT b.id) FROM stu_spacecraft b
                    WHERE sm.id = b.location_id
                    AND NOT EXISTS (SELECT ss.id
                                        FROM stu_spacecraft_system ss
                                        WHERE b.id = ss.spacecraft_id
                                        AND ss.system_type = :cloakSystemId
                                        AND ss.mode > 1)) AS spacecraftcount,
                (SELECT count(DISTINCT c.id) FROM stu_spacecraft c
                    WHERE sm.id = c.location_id
                    AND EXISTS (SELECT ss2.id
                                        FROM stu_spacecraft_system ss2
                                        WHERE c.id = ss2.spacecraft_id
                                        AND ss2.system_type = :cloakSystemId
                                        AND ss2.mode > 1)) AS cloakcount,
                (SELECT mft.effects FROM stu_map_ftypes mft
                WHERE l.field_id = mft.id) as effects
            FROM stu_sys_map sm
            JOIN stu_location l
            ON sm.id = l.id
            WHERE sm.sx BETWEEN :xStart AND :xEnd AND sm.sy BETWEEN :yStart AND :yEnd
            AND sm.systems_id = :systemId
            GROUP BY sm.id, sm.sy, sm.sx, l.field_id',
            $rsm
        )->setParameters([
            'xStart' => $boundaries->getMinX(),
            'xEnd' => $boundaries->getMaxX(),
            'yStart' => $boundaries->getMinY(),
            'yEnd' => $boundaries->getMaxY(),
            'systemId' => $boundaries->getParentId(),
            'cloakSystemId' => SpacecraftSystemTypeEnum::CLOAK->value
        ])->getResult();
    }

    #[\Override]
    public function getColonyShieldData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT sm.sx as x, sm.sy AS y,
            (SELECT COUNT(*) > 0
                FROM stu_colony col
                JOIN stu_colonies_fielddata cfd
                ON col.id = cfd.colonies_id
                WHERE sm.id = col.starsystem_map_id
                AND cfd.aktiv = :active
                AND cfd.buildings_id IN (
                    SELECT bf.buildings_id
                    FROM stu_buildings_functions bf
                    WHERE bf.function = :shieldBuilding)) AS shieldstate
            FROM stu_sys_map sm
            WHERE sm.systems_id = :systemId
            AND sm.sx BETWEEN :xStart AND :xEnd AND sm.sy BETWEEN :yStart AND :yEnd',
            $rsm
        )->setParameters([
            'xStart' => $boundaries->getMinX(),
            'xEnd' => $boundaries->getMaxX(),
            'yStart' => $boundaries->getMinY(),
            'yEnd' => $boundaries->getMaxY(),
            'systemId' => $boundaries->getParentId(),
            'active' => 1,
            'shieldBuilding' => BuildingFunctionEnum::SHIELD_GENERATOR
        ])->getResult();
    }

    #[\Override]
    public function getNormalBorderData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT sm.sx AS x, sm.sy AS y
            FROM stu_sys_map sm
            WHERE sm.systems_id = :systemId
            AND sm.sx BETWEEN :xStart AND :xEnd
            AND sm.sy BETWEEN :yStart AND :yEnd',
            $rsm
        )->setParameters([
            'xStart' => $boundaries->getMinX(),
            'xEnd' => $boundaries->getMaxX(),
            'yStart' => $boundaries->getMinY(),
            'yEnd' => $boundaries->getMaxY(),
            'systemId' => $boundaries->getParentId()
        ])->getResult();
    }

    #[\Override]
    public function getRegionBorderData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT sm.sx AS x, sm.sy AS y
            FROM stu_sys_map sm
            WHERE sm.systems_id = :systemId
            AND sm.sx BETWEEN :xStart AND :xEnd
            AND sm.sy BETWEEN :yStart AND :yEnd',
            $rsm
        )->setParameters([
            'xStart' => $boundaries->getMinX(),
            'xEnd' => $boundaries->getMaxX(),
            'yStart' => $boundaries->getMinY(),
            'yEnd' => $boundaries->getMaxY(),
            'systemId' => $boundaries->getParentId()
        ])->getResult();
    }

    // TODO: Show impassable only for cartographed systems. 
    // Currently, it does not display any impassable areas.
    #[\Override]
    public function getImpassableBorderData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT sm.sx AS x, sm.sy AS y, TRUE AS impassable,
                    (SELECT mft.complementary_color FROM stu_map_ftypes mft JOIN stu_location l on l.id = sm.id where mft.id = l.field_id) AS complementary_color
            FROM stu_sys_map sm
            WHERE sm.systems_id = :systemId
            AND sm.sx BETWEEN :xStart AND :xEnd
            AND sm.sy BETWEEN :yStart AND :yEnd',
            $rsm
        )->setParameters([
            'xStart' => $boundaries->getMinX(),
            'xEnd' => $boundaries->getMaxX(),
            'yStart' => $boundaries->getMinY(),
            'yEnd' => $boundaries->getMaxY(),
            'systemId' => $boundaries->getParentId()
        ])->getResult();
    }

    #[\Override]
    public function getCartographingData(PanelBoundaries $boundaries, ResultSetMapping $rsm, array $locations): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT DISTINCT 
                sm.sx AS x, 
                sm.sy AS y, 
                CASE 
                    WHEN sm.id IN (:fieldIds) THEN TRUE ELSE FALSE
                END AS cartographing,
                    (SELECT mft.complementary_color FROM stu_map_ftypes mft JOIN stu_location l on l.id = sm.id where mft.id = l.field_id) AS complementary_color
            FROM stu_sys_map sm
            WHERE sm.sx BETWEEN :xStart AND :xEnd
            AND sm.sy BETWEEN :yStart AND :yEnd
            AND sm.systems_id = :systemId
            ORDER BY cartographing DESC',
            $rsm
        )->setParameters([
            'xStart' => $boundaries->getMinX(),
            'xEnd' => $boundaries->getMaxX(),
            'yStart' => $boundaries->getMinY(),
            'yEnd' => $boundaries->getMaxY(),
            'systemId' => $boundaries->getParentId(),
            'fieldIds' => $locations
        ])->getResult();
    }


    #[\Override]
    public function getAnomalyData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT sm.sx AS x, sm.sy AS y,
                (SELECT array_to_string(array(SELECT a.anomaly_type_id FROM stu_anomaly a WHERE a.location_id = sm.id), \',\')) as anomalytypes
            FROM stu_sys_map sm
            WHERE sm.systems_id = :systemId
            AND sm.sx BETWEEN :xStart AND :xEnd
            AND sm.sy BETWEEN :yStart AND :yEnd',
            $rsm
        )->setParameters([
            'xStart' => $boundaries->getMinX(),
            'xEnd' => $boundaries->getMaxX(),
            'yStart' => $boundaries->getMinY(),
            'yEnd' => $boundaries->getMaxY(),
            'systemId' => $boundaries->getParentId()
        ])->getResult();
    }

    #[\Override]
    public function getLssBlockadeLocations(PanelBoundaries $boundaries): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('sx', 'x', 'integer');
        $rsm->addScalarResult('sy', 'y', 'integer');
        $rsm->addScalarResult('effects', 'effects', 'string');

        return $this->getEntityManager()->createNativeQuery(
            'WITH bbox AS (
                SELECT id, sx, sy
                FROM stu_sys_map
                WHERE systems_id = :systemId
                AND sx BETWEEN :xStart AND :xEnd
                AND sy BETWEEN :yStart AND :yEnd
            )
            SELECT sm.sx, sm.sy, mft.effects
            FROM bbox sm
            JOIN stu_location l ON sm.id = l.id
            JOIN stu_map_ftypes mft ON mft.id   = l.field_id',
            $rsm
        )->setParameters([
            'xStart' => $boundaries->getMinX(),
            'xEnd' => $boundaries->getMaxX(),
            'yStart' => $boundaries->getMinY(),
            'yEnd' => $boundaries->getMaxY(),
            'systemId' => $boundaries->getParentId()
        ])->getResult();
    }

    #[\Override]
    public function getIgnoringSubspaceLayerData(PanelBoundaries $boundaries, int $ignoreUserId, int $time, ResultSetMapping $rsm): array
    {
        $maxAge = $time - FlightSignatureVisibilityEnum::SIG_VISIBILITY_UNCLOAKED;

        return $this->getEntityManager()->createNativeQuery(
            'SELECT sm.sx as x, sm.sy AS y, mft.effects as effects,
                (select count(distinct fs1.ship_id) from stu_flight_sig fs1
                where fs1.location_id = sm.id
                AND fs1.user_id != :ignoreUserId
                AND (fs1.from_direction = 1 OR fs1.to_direction = 1)
                AND fs1.time > :timeThreshold) as d1c,
                (select count(distinct fs2.ship_id) from stu_flight_sig fs2
                where fs2.location_id = sm.id
                AND fs2.user_id != :ignoreUserId
                AND (fs2.from_direction = 2 OR fs2.to_direction = 2)
                AND fs2.time > :timeThreshold) as d2c,
                (select count(distinct fs3.ship_id) from stu_flight_sig fs3
                where fs3.location_id = sm.id
                AND fs3.user_id != :ignoreUserId
                AND (fs3.from_direction = 3 OR fs3.to_direction = 3)
                AND fs3.time > :timeThreshold) as d3c,
                (select count(distinct fs4.ship_id) from stu_flight_sig fs4
                where fs4.location_id = sm.id
                AND fs4.user_id != :ignoreUserId
                AND (fs4.from_direction = 4 OR fs4.to_direction = 4)
                AND fs4.time > :timeThreshold) as d4c 
                FROM stu_sys_map sm
                JOIN stu_location l
                ON sm.id = l.id
                JOIN stu_map_ftypes mft
                ON l.field_id = mft.id
                WHERE sm.systems_id = :systemId
                AND sm.sx BETWEEN :xStart AND :xEnd AND sm.sy BETWEEN :yStart AND :yEnd',
            $rsm
        )->setParameters([
            'xStart' => $boundaries->getMinX(),
            'xEnd' => $boundaries->getMaxX(),
            'yStart' => $boundaries->getMinY(),
            'yEnd' => $boundaries->getMaxY(),
            'systemId' => $boundaries->getParentId(),
            'ignoreUserId' => $ignoreUserId,
            'timeThreshold' => $maxAge
        ])->getResult();
    }
    #[\Override]
    public function getShipSubspaceLayerData(PanelBoundaries $boundaries, int $shipId, int $time, ResultSetMapping $rsm, ?int $rumpId = null): array
    {
        $maxAge = $time - FlightSignatureVisibilityEnum::SIG_VISIBILITY_UNCLOAKED;

        $rumpId_condition = $rumpId !== null ? 'AND fs1.rump_id = :rumpId' : '';
        $rumpId_condition2 = $rumpId !== null ? 'AND fs2.rump_id = :rumpId' : '';
        $rumpId_condition3 = $rumpId !== null ? 'AND fs3.rump_id = :rumpId' : '';
        $rumpId_condition4 = $rumpId !== null ? 'AND fs4.rump_id = :rumpId' : '';

        $query = $this->getEntityManager()->createNativeQuery(
            sprintf(
                'SELECT sm.sx as x, sm.sy as y, mft.effects as effects,
            (SELECT count(distinct fs1.ship_id) from stu_flight_sig fs1
                WHERE fs1.location_id = l.id
                AND fs1.ship_id = :shipId %s
                AND (fs1.from_direction = 1 OR fs1.to_direction = 1)
                AND fs1.time > :timeThreshold AND fs1.is_cloaked = :false) as d1c,
            (SELECT count(distinct fs2.ship_id) from stu_flight_sig fs2
                WHERE fs2.location_id = l.id
                AND fs2.ship_id = :shipId %s
                AND (fs2.from_direction = 2 OR fs2.to_direction = 2)
                AND fs2.time > :timeThreshold AND fs2.is_cloaked = :false) as d2c,
            (SELECT count(distinct fs3.ship_id) from stu_flight_sig fs3
                WHERE fs3.location_id = l.id
                AND fs3.ship_id = :shipId %s
                AND (fs3.from_direction = 3 OR fs3.to_direction = 3)
                AND fs3.time > :timeThreshold AND fs3.is_cloaked = :false) as d3c,
            (SELECT count(distinct fs4.ship_id) from stu_flight_sig fs4
                WHERE fs4.location_id = l.id
                AND fs4.ship_id = :shipId %s
                AND (fs4.from_direction = 4 OR fs4.to_direction = 4)
                AND fs4.time > :timeThreshold AND fs4.is_cloaked = :false) as d4c 
            FROM stu_sys_map sm
                JOIN stu_location l
                ON sm.id = l.id
                JOIN stu_map_ftypes mft
                ON l.field_id = mft.id
                WHERE sm.systems_id = :systemId
                AND sm.sx BETWEEN :xStart AND :xEnd AND sm.sy BETWEEN :yStart AND :yEnd',
                $rumpId_condition,
                $rumpId_condition2,
                $rumpId_condition3,
                $rumpId_condition4
            ),
            $rsm
        );
        $parameters = [
            'xStart' => $boundaries->getMinX(),
            'xEnd' => $boundaries->getMaxX(),
            'yStart' => $boundaries->getMinY(),
            'yEnd' => $boundaries->getMaxY(),
            'systemId' => $boundaries->getParentId(),
            'shipId' => $shipId,
            'timeThreshold' => $maxAge,
            'false' => false
        ];

        if ($rumpId !== null) {
            $parameters['rumpId'] = $rumpId;
        }

        return $query->setParameters($parameters)->getResult();
    }

    #[\Override]
    public function getRandomSystemMapIdsForAstroMeasurement(int $starSystemId, int $location): array
    {
        $result = [];

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id', 'integer');

        $userColonyFields = $this->getEntityManager()
            ->createNativeQuery(
                'SELECT sm.id as id
                FROM stu_sys_map sm
                WHERE sm.systems_id = :systemId
                AND sm.id != :location
                AND EXISTS (SELECT c.id
                            FROM stu_colony c
                            WHERE c.starsystem_map_id = sm.id
                            AND c.user_id != :noOne)
                ORDER BY RANDOM()
                LIMIT 2',
                $rsm
            )
            ->setParameters([
                'systemId' => $starSystemId,
                'location'  => $location,
                'noOne' => UserConstants::USER_NOONE
            ])
            ->getResult();

        $result = array_merge($result, $userColonyFields);

        $otherColonyFields = $this->getEntityManager()
            ->createNativeQuery(
                'SELECT sm.id as id
                FROM stu_sys_map sm
                JOIN stu_location l
                ON sm.id = l.id
                JOIN stu_map_ftypes ft
                ON l.field_id = ft.id
                JOIN stu_colonies_classes cc
                ON ft.colonies_classes_id = cc.id
                WHERE sm.systems_id = :systemId
                AND ft.colonies_classes_id IS NOT NULL
                AND cc.type < 3
                AND sm.id NOT IN (:ids)
                ORDER BY RANDOM()
                LIMIT :theLimit',
                $rsm
            )
            ->setParameters([
                'systemId' => $starSystemId,
                'ids' => $result !== [] ? $result : [0],
                'theLimit' => AstronomicalEntry::MEASUREMENT_COUNT - count($result)
            ])
            ->getResult();

        $result = array_merge($result, $otherColonyFields);

        if (count($result) < AstronomicalEntry::MEASUREMENT_COUNT) {
            $otherFields = $this->getEntityManager()
                ->createNativeQuery(
                    'SELECT sm.id as id
                    FROM stu_sys_map sm
                    JOIN stu_location l
                    ON sm.id = l.id
                    JOIN stu_map_ftypes ft
                    ON l.field_id = ft.id
                    WHERE sm.systems_id = :systemId
                    AND ft.x_damage_system <= 10
                    AND ft.x_damage <= 10
                    ORDER BY RANDOM()
                    LIMIT :theLimit',
                    $rsm
                )
                ->setParameters([
                    'systemId' => $starSystemId,
                    'theLimit' => AstronomicalEntry::MEASUREMENT_COUNT - count($result)
                ])
                ->getResult();

            $result = array_merge($result, $otherFields);
        }

        return array_map(fn(array $data) => $data['id'], $result);
    }

    #[\Override]
    public function prototype(): StarSystemMap
    {
        return new StarSystemMap();
    }

    #[\Override]
    public function save(StarSystemMap $starSystemMap): void
    {
        $em = $this->getEntityManager();

        $em->persist($starSystemMap);
    }

    #[\Override]
    public function truncateByStarSystem(StarSystem $starSystem): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s sm WHERE sm.systems_id = :systemId',
                StarSystemMap::class
            )
        )
            ->setParameters(['systemId' => $starSystem->getId()])
            ->execute();
    }
}
