<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

use Stu\Component\Game\GameEnum;
use Stu\Component\Ship\AstronomicalMappingEnum;
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

    public function save(StarSystemMapInterface $starSystemMap): void
    {
        $em = $this->getEntityManager();

        $em->persist($starSystemMap);
    }
}
