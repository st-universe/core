<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Override;
use Stu\Orm\Entity\Anomaly;
use Stu\Orm\Entity\AnomalyInterface;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\ShipInterface;

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
        $em = $this->getEntityManager();

        $em->remove($anomaly);
    }

    /**
     * @return array<AnomalyInterface>
     */
    #[Override]
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

    #[Override]
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
