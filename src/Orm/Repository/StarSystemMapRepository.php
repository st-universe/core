<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

use Stu\Component\Game\GameEnum;
use Stu\Component\Ship\AstronomicalMappingEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\StarSystemMapInterface;

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
        int $endSy
    ): array {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT m FROM %s m
                    WHERE m.systems_id = :starSystemId AND
                        m.sx BETWEEN :startSx AND :endSx AND
                        m.sy BETWEEN :startSy AND :endSy
                    ORDER BY m.sy, m.sx',
                    StarSystemMap::class
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

    public function getRandomFieldsForAstroMeasurement(int $starSystemId): array
    {
        $result = [];

        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(StarSystemMap::class, 'm');
        $rsm->addFieldResult('m', 'id', 'id');
        $rsm->addFieldResult('m', 'sx', 'sx');
        $rsm->addFieldResult('m', 'sy', 'sy');
        $rsm->addFieldResult('m', 'systems_id', 'systems_id');
        $rsm->addFieldResult('m', 'field_id', 'field_id');

        $userColonyFields = $this->getEntityManager()
            ->createNativeQuery(
                'SELECT sm.id as id, sm.sx as sx, sm.sy as sy, sm.systems_id as systems_id, sm.field_id as field_id
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
                'noOne' => GameEnum::USER_NOONE
            ])
            ->getResult();

        $result = array_merge($result, $userColonyFields);

        $otherColonyFields = $this->getEntityManager()
            ->createNativeQuery(
                'SELECT sm.id as id, sm.sx as sx, sm.sy as sy, sm.systems_id as systems_id, sm.field_id as field_id
                FROM stu_sys_map sm
                JOIN stu_map_ftypes ft
                ON sm.field_id = ft.id
                WHERE sm.systems_id = :systemId
                AND ft.colonies_classes_id IS NOT NULL
                AND sm.id NOT IN (:ids)
                ORDER BY RANDOM()
                LIMIT :theLimit',
                $rsm
            )
            ->setParameters([
                'systemId' => $starSystemId,
                'ids' => count($result) > 0 ? $result : [0],
                'theLimit' => AstronomicalMappingEnum::MEASUREMENT_COUNT - count($result)
            ])
            ->getResult();

        $result = array_merge($result, $otherColonyFields);

        if (count($result) < AstronomicalMappingEnum::MEASUREMENT_COUNT) {
            $otherFields = $this->getEntityManager()
                ->createNativeQuery(
                    'SELECT sm.id as id, sm.sx as sx, sm.sy as sy, sm.systems_id as systems_id, sm.field_id as field_id 
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

        return $result;
    }

    public function getRumpCategoryInfo(int $cx, int $cy): array
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
                WHERE s.cx = :cx
                AND s.cy = :cy
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
                'cx' => $cx,
                'cy' => $cy,
                'systemId' => ShipSystemTypeEnum::SYSTEM_CLOAK
            ])
            ->getResult();
    }

    public function save(StarSystemMapInterface $starSystemMap): void
    {
        $em = $this->getEntityManager();

        $em->persist($starSystemMap);
    }

    public function getRandomPassableUnoccupiedWithoutDamage(): int
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id', 'integer');

        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT sm.id
                FROM stu_sys_map sm
                JOIN stu_map_ftypes mft
                ON sm.field_id = mft.id
                WHERE NOT EXISTS (SELECT s.id FROM stu_ships s WHERE s.starsystem_map_id = sm.id)
                AND mft.x_damage = 0
                AND mft.passable = true
                ORDER BY RANDOM()
                LIMIT 1',
                $rsm
            )
            ->getSingleScalarResult();
    }
}
