<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\Query\ResultSetMapping;
use RuntimeException;
use Stu\Component\Map\MapEnum;
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\MapFieldType;

trait MapRandomLocationTrait
{
    #[\Override]
    public function getRandomPassableUnoccupiedWithoutDamage(Layer $layer, bool $isAtBorder = false): Map
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id', 'integer');

        $borderCriteria = $isAtBorder
            ? sprintf(
                'AND (l.cx in (1, %d) OR l.cy in (1, %d))',
                $layer->getWidth(),
                $layer->getHeight()
            ) : '';

        $randomMapId = (int) $this->getEntityManager()
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

    #[\Override]
    public function getRandomPirateSpawnLocation(Layer $layer): ?Map
    {
        $fieldTypes = array_filter(
            $this->getEntityManager()->getRepository(MapFieldType::class)->findBy([
                'passable' => true,
                'x_damage' => 0
            ]),
            static fn (MapFieldType $fieldType): bool => !$fieldType->hasEffect(FieldTypeEffectEnum::NO_PIRATES)
        );

        if ($fieldTypes === []) {
            return null;
        }

        $queryBuilder = $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.layer = :layer')
            ->andWhere('m.mapFieldType IN (:fieldTypes)')
            ->andWhere('m.spacecrafts IS EMPTY')
            ->andWhere('(m.admin_region_id IS NULL OR m.admin_region_id NOT IN (:forbiddenAdminAreas))')
            ->setParameter('layer', $layer)
            ->setParameter('fieldTypes', array_values($fieldTypes))
            ->setParameter('forbiddenAdminAreas', MapEnum::PIRATE_FORBIDDEN_ADMIN_AREAS);

        $count = (int) $queryBuilder->getQuery()->getSingleScalarResult();
        if ($count === 0) {
            return null;
        }

        return $queryBuilder
            ->select('m')
            ->orderBy('m.id')
            ->setFirstResult(random_int(0, $count - 1))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
