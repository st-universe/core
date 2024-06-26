<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Component\Anomaly\Type\SubspaceEllipseHandler;
use Stu\Component\Building\BuildingEnum;
use Stu\Component\Ship\AstronomicalMappingEnum;
use Stu\Component\Ship\FlightSignatureVisibilityEnum;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\StarSystemMapInterface;

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

    public function getByCoordinates(
        int $starSystemId,
        int $sx,
        int $sy
    ): ?StarSystemMapInterface {
        return $this->findOneBy([
            'systems_id' => $starSystemId,
            'sx' => $sx,
            'sy' => $sy
        ]);
    }

    public function getByCoordinateRange(
        StarSystemInterface $starSystem,
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
                'starSystemId' => $starSystem,
                'startSx' => $startSx,
                'endSx' => $endSx,
                'startSy' => $startSy,
                'endSy' => $endSy
            ])
            ->getResult();
    }

    public function getMapLayerData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT sm.sx as x, sm.sy AS y, ft.type
                FROM stu_sys_map sm
                JOIN stu_map_ftypes ft ON ft.id = sm.field_id
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

    public function getShipCountLayerData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT sm.id, sm.sx as x, sm.sy AS y,
                (SELECT count(DISTINCT b.id) FROM stu_ships b
                    WHERE sm.id = b.starsystem_map_id
                    AND NOT EXISTS (SELECT ss.id
                                        FROM stu_ship_system ss
                                        WHERE b.id = ss.ship_id
                                        AND ss.system_type = :cloakSystemId
                                        AND ss.mode > 1)) AS shipcount,
                (SELECT count(DISTINCT c.id) FROM stu_ships c
                    WHERE sm.id = c.starsystem_map_id
                    AND EXISTS (SELECT ss2.id
                                        FROM stu_ship_system ss2
                                        WHERE c.id = ss2.ship_id
                                        AND ss2.system_type = :cloakSystemId
                                        AND ss2.mode > 1)) AS cloakcount
            FROM stu_sys_map sm
            WHERE sm.sx BETWEEN :xStart AND :xEnd AND sm.sy BETWEEN :yStart AND :yEnd
            AND sm.systems_id = :systemId
            GROUP BY sm.id, sm.sy, sm.sx',
            $rsm
        )->setParameters([
            'xStart' => $boundaries->getMinX(),
            'xEnd' => $boundaries->getMaxX(),
            'yStart' => $boundaries->getMinY(),
            'yEnd' => $boundaries->getMaxY(),
            'systemId' => $boundaries->getParentId(),
            'cloakSystemId' => ShipSystemTypeEnum::SYSTEM_CLOAK->value
        ])->getResult();
    }


    public function getColonyShieldData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT sm.sx as x, sm.sy AS y,
            (SELECT COUNT(cfd) > 0
                FROM stu_colonies col
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
            'shieldBuilding' => BuildingEnum::BUILDING_FUNCTION_SHIELD_GENERATOR
        ])->getResult();
    }


    public function getBorderData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array
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

    public function getIgnoringSubspaceLayerData(PanelBoundaries $boundaries, int $ignoreId, ResultSetMapping $rsm): array
    {
        $maxAge = time() - FlightSignatureVisibilityEnum::SIG_VISIBILITY_UNCLOAKED;

        return $this->getEntityManager()->createNativeQuery(
            sprintf(
                'SELECT sm.sx as x, sm.sy AS y,
                (select count(distinct fs1.ship_id) from stu_flight_sig fs1
                where fs1.starsystem_map_id = sm.id
                AND fs1.user_id != %1$d
                AND (fs1.from_direction = 1 OR fs1.to_direction = 1)
                AND fs1.time > %2$d) as d1c,
                (select count(distinct fs2.ship_id) from stu_flight_sig fs2
                where fs2.starsystem_map_id = sm.id
                AND fs2.user_id != %1$d
                AND (fs2.from_direction = 2 OR fs2.to_direction = 2)
                AND fs2.time > %2$d) as d2c,
                (select count(distinct fs3.ship_id) from stu_flight_sig fs3
                where fs3.starsystem_map_id = sm.id
                AND fs3.user_id != %1$d
                AND (fs3.from_direction = 3 OR fs3.to_direction = 3)
                AND fs3.time > %2$d) as d3c,
                (select count(distinct fs4.ship_id) from stu_flight_sig fs4
                where fs4.starsystem_map_id = sm.id
                AND fs4.user_id != %1$d
                AND (fs4.from_direction = 4 OR fs4.to_direction = 4)
                AND fs4.time > %2$d) as d4c 
                FROM stu_sys_map sm
                WHERE sm.systems_id = :systemId
                AND sm.sx BETWEEN :xStart AND :xEnd AND sm.sy BETWEEN :yStart AND :yEnd',
                $ignoreId,
                $maxAge
            ),
            $rsm
        )->setParameters([
            'xStart' => $boundaries->getMinX(),
            'xEnd' => $boundaries->getMaxX(),
            'yStart' => $boundaries->getMinY(),
            'yEnd' => $boundaries->getMaxY(),
            'systemId' => $boundaries->getParentId()
        ])->getResult();
    }

    public function getRandomSystemMapIdsForAstroMeasurement(int $starSystemId): array
    {
        $result = [];

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id', 'integer');

        $userColonyFields = $this->getEntityManager()
            ->createNativeQuery(
                'SELECT sm.id as id
                FROM stu_sys_map sm
                WHERE sm.systems_id = :systemId
                AND EXISTS (SELECT c.id
                            FROM stu_colonies c
                            WHERE c.starsystem_map_id = sm.id
                            AND c.user_id != :noOne)
                ORDER BY RANDOM()
                LIMIT 2',
                $rsm
            )
            ->setParameters([
                'systemId' => $starSystemId,
                'noOne' => UserEnum::USER_NOONE
            ])
            ->getResult();

        $result = array_merge($result, $userColonyFields);

        $otherColonyFields = $this->getEntityManager()
            ->createNativeQuery(
                'SELECT sm.id as id
                FROM stu_sys_map sm
                JOIN stu_map_ftypes ft
                ON sm.field_id = ft.id
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
                'theLimit' => AstronomicalMappingEnum::MEASUREMENT_COUNT - count($result)
            ])
            ->getResult();

        $result = array_merge($result, $otherColonyFields);

        if (count($result) < AstronomicalMappingEnum::MEASUREMENT_COUNT) {
            $otherFields = $this->getEntityManager()
                ->createNativeQuery(
                    'SELECT sm.id as id
                    FROM stu_sys_map sm
                    JOIN stu_map_ftypes ft
                    ON sm.field_id = ft.id
                    WHERE sm.systems_id = :systemId
                    AND ft.x_damage_system <= 10
                    AND ft.x_damage <= 10
                    ORDER BY RANDOM()
                    LIMIT :theLimit',
                    $rsm
                )
                ->setParameters([
                    'systemId' => $starSystemId,
                    'theLimit' => AstronomicalMappingEnum::MEASUREMENT_COUNT - count($result)
                ])
                ->getResult();

            $result = array_merge($result, $otherFields);
        }

        return array_map(fn (array $data) => $data['id'], $result);
    }

    public function getRumpCategoryInfo(LayerInterface $layer, int $cx, int $cy): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('category_name', 'category_name', 'string');
        $rsm->addScalarResult('amount', 'amount', 'integer');

        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT rc.name as category_name, count(*) as amount
                FROM stu_ships s
                JOIN stu_rumps r
                ON s.rumps_id = r.id
                JOIN stu_rumps_categories rc
                ON r.category_id = rc.id
                LEFT JOIN stu_map m
                ON s.map_id = m.id
                LEFT JOIN stu_sys_map sm 
                ON s.starsystem_map_id = sm.id
                LEFT JOIN stu_systems sys
                ON sm.systems_id = sys.id
                LEFT JOIN stu_map m2
                ON m2.systems_id = sys.id
                WHERE (         m.layer_id = :layerId
                            AND m.cx = :cx
                            AND m.cy = :cy
                        OR      m2.layer_id = :layerId
                            AND m2.cx = :cx
                            AND m2.cy = :cy)
                AND NOT EXISTS (SELECT ss.id
                                    FROM stu_ship_system ss
                                    WHERE s.id = ss.ship_id
                                    AND ss.system_type = :systemId
                                    AND ss.mode > 1)
                GROUP BY rc.name
                ORDER BY 2 desc',
                $rsm
            )
            ->setParameters([
                'layerId' => $layer->getId(),
                'cx' => $cx,
                'cy' => $cy,
                'systemId' => ShipSystemTypeEnum::SYSTEM_CLOAK
            ])
            ->getResult();
    }

    public function prototype(): StarSystemMapInterface
    {
        return new StarSystemMap();
    }

    public function save(StarSystemMapInterface $starSystemMap): void
    {
        $em = $this->getEntityManager();

        $em->persist($starSystemMap);
    }

    public function getForSubspaceEllipseCreation(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('sys_map_id', 'sys_map_id', 'integer');
        $rsm->addScalarResult('descriminator', 'descriminator', 'integer');

        $sysMapIds = $this->getEntityManager()
            ->createNativeQuery(
                'SELECT sys_map_id, descriminator FROM (
                    SELECT coalesce(sum(r1.tractor_mass) / 10, 0)
                            + coalesce(sum(r2.tractor_mass), 0)
                            + coalesce((SELECT count(ca.id)
                                            FROM stu_crew_assign ca
                                            JOIN stu_ships s
                                            ON ca.ship_id = s.id
                                            WHERE s.user_id >= :firstUserId
                                            AND s.state != :state
                                            AND s.starsystem_map_id = sm.id)
                                        * (SELECT count(ss.id)
                                            FROM stu_ship_system ss
                                            JOIN stu_ships s
                                            ON ss.ship_id = s.id
                                            WHERE s.user_id >= :firstUserId
                                            AND s.state != :state
                                            AND s.starsystem_map_id = sm.id
                                            AND ss.mode > :mode)
                                        * 100, 0) - :threshold AS descriminator,
                        sm.id AS sys_map_id FROM stu_sys_map sm
                        JOIN stu_ships s
                        ON s.starsystem_map_id = sm.id
                        LEFT JOIN stu_rumps r1
                        ON s.rumps_id = r1.id
                        AND r1.category_id = :rumpCategory
                        LEFT JOIN stu_rumps r2
                        ON s.rumps_id = r2.id
                        AND r2.category_id != :rumpCategory
                        WHERE s.user_id >= :firstUserId
                        AND s.state != :state
                        GROUP BY sm.id) AS foo
                    WHERE descriminator > 0',
                $rsm
            )
            ->setParameters([
                'threshold' => SubspaceEllipseHandler::MASS_CALCULATION_THRESHOLD,
                'rumpCategory' => ShipRumpEnum::SHIP_CATEGORY_STATION,
                'firstUserId' => UserEnum::USER_FIRST_ID,
                'mode' => ShipSystemModeEnum::MODE_OFF,
                'state' => ShipStateEnum::SHIP_STATE_UNDER_CONSTRUCTION
            ])
            ->getResult();

        $finalIds = [];
        foreach ($sysMapIds as $entry) {
            $descriminator = $entry['descriminator'];

            if ((int)ceil($descriminator / 1_000_000 + 5) > random_int(1, 100)) {
                $finalIds[] = $entry['sys_map_id'];
            }
        }

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT sm FROM %s sm
                    WHERE sm.id in (:ids)',
                    StarSystemMap::class
                )
            )
            ->setParameters([
                'ids' => $finalIds
            ])
            ->getResult();
    }

    public function truncateByStarSystem(StarSystemInterface $starSystem): void
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
