<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Stu\Orm\Entity\Anomaly;
use Stu\Orm\Entity\AnomalyInterface;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\ShipInterface;

/**
 * @extends EntityRepository<Anomaly>
 */
final class AnomalyRepository extends EntityRepository implements AnomalyRepositoryInterface
{
    public function prototype(): AnomalyInterface
    {
        return new Anomaly();
    }

    public function save(AnomalyInterface $anomaly): void
    {
        $em = $this->getEntityManager();

        $em->persist($anomaly);
    }

    public function delete(AnomalyInterface $anomaly): void
    {
        $em = $this->getEntityManager();

        $em->remove($anomaly);
    }

    /**
     * @return array<AnomalyInterface>
     */
    public function findAllActive(): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT a
                        FROM %s a
                        WHERE a.remaining_ticks > 0',
                    Anomaly::class
                )
            )
            ->getResult();
    }

    public function getClosestAnomalyDistance(ShipInterface $ship): ?int
    {
        $map = $ship->getMap();
        if ($map === null) {
            return null;
        }

        $range = $ship->getSensorRange() * 2;

        try {
            $result = (int)$this->getEntityManager()->createQuery(
                sprintf(
                    'SELECT SQRT(ABS(m.cx - :x) * ABS(m.cx - :x) + ABS(m.cy - :y) * ABS(m.cy - :y)) as foo
                    FROM %s a
                    JOIN %s m
                    WITH a.map_id = m.id
                    WHERE (m.cx BETWEEN :startX AND :endX)
                    AND (m.cy BETWEEN :startY AND :endY)
                    AND m.layer = :layer
                    ORDER BY foo ASC',
                    Anomaly::class,
                    Map::class
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
