<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Override;
use Stu\Component\Building\BuildingEnum;
use Stu\Component\Ship\AstronomicalMappingEnum;
use Stu\Component\Ship\FlightSignatureVisibilityEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\StarSystemMapInterface;

/**
 * @extends EntityRepository<StarSystemMap>
 */
final class StarSystemMapRepository extends EntityRepository implements StarSystemMapRepositoryInterface
{
    #[Override]
    public function getBySystemOrdered(int $starSystemId): array
    {
        return $this->findBy(
            ['systems_id' => $starSystemId],
            ['sy' => 'asc', 'sx' => 'asc']
        );
    }

    #[Override]
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

    #[Override]
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

    #[Override]
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

    #[Override]
    public function getShipCountLayerData(PanelBoundaries $boundaries, ResultSetMapping $rsm): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT sm.id, sm.sx as x, sm.sy AS y,
                (SELECT count(DISTINCT b.id) FROM stu_ships b
                    WHERE sm.id = b.location_id
                    AND NOT EXISTS (SELECT ss.id
                                        FROM stu_ship_system ss
                                        WHERE b.id = ss.ship_id
                                        AND ss.system_type = :cloakSystemId
                                        AND ss.mode > 1)) AS shipcount,
                (SELECT count(DISTINCT c.id) FROM stu_ships c
                    WHERE sm.id = c.location_id
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

    #[Override]
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


    #[Override]
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

    #[Override]
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

    #[Override]
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
                'theLimit' => AstronomicalMappingEnum::MEASUREMENT_COUNT - count($result)
            ])
            ->getResult();

        $result = array_merge($result, $otherColonyFields);

        if (count($result) < AstronomicalMappingEnum::MEASUREMENT_COUNT) {
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
                    'theLimit' => AstronomicalMappingEnum::MEASUREMENT_COUNT - count($result)
                ])
                ->getResult();

            $result = array_merge($result, $otherFields);
        }

        return array_map(fn (array $data) => $data['id'], $result);
    }

    #[Override]
    public function prototype(): StarSystemMapInterface
    {
        return new StarSystemMap();
    }

    #[Override]
    public function save(StarSystemMapInterface $starSystemMap): void
    {
        $em = $this->getEntityManager();

        $em->persist($starSystemMap);
    }

    #[Override]
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
