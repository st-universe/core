<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Anomaly;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Map;

/**
 * @extends EntityRepository<Anomaly>
 */
final class AnomalyRepository extends EntityRepository implements AnomalyRepositoryInterface
{
    #[\Override]
    public function prototype(): Anomaly
    {
        return new Anomaly();
    }

    #[\Override]
    public function save(Anomaly $anomaly): void
    {
        $em = $this->getEntityManager();
        $em->persist($anomaly);
    }

    #[\Override]
    public function delete(Anomaly $anomaly): void
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

    #[\Override]
    public function getByLocationAndType(Location $location, AnomalyTypeEnum $type): ?Anomaly
    {
        return $this->findOneBy([
            'location' => $location,
            'anomaly_type_id' => $type->value
        ]);
    }

    /**
     * @return array<Anomaly>
     */
    #[\Override]
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

    #[\Override]
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

    #[\Override]
    public function getClosestAnomalyDistance(SpacecraftWrapperInterface $wrapper): ?int
    {
        $map = $wrapper->get()->getMap();
        if ($map === null) {
            return null;
        }

        $range = 2 * ($wrapper->getLssSystemData()?->getSensorRange() ?? 0);

        try {
            $result = (int)$this->getEntityManager()->createQuery(
                sprintf(
                    'SELECT SQRT(ABS(l.cx - :x) * ABS(l.cx - :x) + ABS(l.cy - :y) * ABS(l.cy - :y)) as foo
                    FROM %s a
                    JOIN %s m
                    WITH a.location = m
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

    #[\Override]
    public function getLocationsWithIonstormAnomalies(Layer $layer): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                'SELECT l FROM %s a
                    JOIN %s l
                    WITH a.location = l
                    WHERE a.anomaly_type_id = :type
                    AND l.layer = :layer',
                    Anomaly::class,
                    Location::class
                )
            )
            ->setParameters([
                'type' => AnomalyTypeEnum::ION_STORM->value,
                'layer' => $layer->getId()
            ])
            ->getResult();
    }
}
