<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Override;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Anomaly;
use Stu\Orm\Entity\AnomalyInterface;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Entity\Map;

/**
 * @extends EntityRepository<Anomaly>
 */
final class AnomalyRepository extends EntityRepository implements AnomalyRepositoryInterface
{
    #[Override]
    public function prototype(): AnomalyInterface
    {
        return new Anomaly();
    }

    #[Override]
    public function save(AnomalyInterface $anomaly): void
    {
        $em = $this->getEntityManager();
        $em->persist($anomaly);
    }

    #[Override]
    public function delete(AnomalyInterface $anomaly): void
    {
        $location = $anomaly->getLocation();
        if ($location !== null) {
            $location->getAnomalies()->removeElement($anomaly);
        }

        $parent = $anomaly->getParent();
        if ($parent !== null) {
            $parent->getChildren()->removeElement($anomaly);
        }

        $this->getEntityManager()->remove($anomaly);
    }

    #[Override]
    public function getByLocationAndType(LocationInterface $location, AnomalyTypeEnum $type): ?AnomalyInterface
    {
        return $this->findOneBy([
            'location_id' => $location->getId(),
            'anomaly_type_id' => $type->value
        ]);
    }

    /**
     * @return array<AnomalyInterface>
     */
    #[Override]
    public function findAllRoot(): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT a FROM %s a
                    WHERE a.parent_id IS NULL',
                    Anomaly::class
                )
            )
            ->getResult();
    }

    #[Override]
    public function getActiveCountByTypeWithoutParent(AnomalyTypeEnum $type): int
    {
        return (int) $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT count(a.id) FROM %s a
                    WHERE a.anomaly_type_id = :type
                    AND a.remaining_ticks > 0
                    AND a.parent_id IS NULL',
                    Anomaly::class
                )
            )
            ->setParameter('type', $type->value)
            ->getSingleScalarResult();
    }

    #[Override]
    public function getClosestAnomalyDistance(SpacecraftWrapperInterface $wrapper): ?int
    {
        $map = $wrapper->get()->getMap();
        if ($map === null) {
            return null;
        }

        $range = $wrapper->getSensorRange() * 2;

        try {
            $result = (int)$this->getEntityManager()->createQuery(
                sprintf(
                    'SELECT SQRT(ABS(l.cx - :x) * ABS(l.cx - :x) + ABS(l.cy - :y) * ABS(l.cy - :y)) as foo
                    FROM %s a
                    JOIN %s m
                    WITH a.location_id = m.id
                    JOIN %s l
                    WITH m.id = l.id
                    WHERE a.remaining_ticks > 0
                    AND (l.cx BETWEEN :startX AND :endX)
                    AND (l.cy BETWEEN :startY AND :endY)
                    AND l.layer = :layer
                    ORDER BY foo ASC',
                    Anomaly::class,
                    Map::class,
                    Location::class
                )
            )
                ->setMaxResults(1)
                ->setParameters([
                    'layer' => $map->getLayer(),
                    'x' => $map->getX(),
                    'y' => $map->getY(),
                    'startX' => $map->getX() - $range,
                    'endX' => $map->getX() +  $range,
                    'startY' => $map->getY() - $range,
                    'endY' => $map->getY() +  $range
                ])
                ->getSingleScalarResult();

            if ($result > $range) {
                return null;
            }

            return $result;
        } catch (NoResultException) {
            return null;
        }
    }
}
