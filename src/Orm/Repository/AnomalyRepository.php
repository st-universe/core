<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\Anomaly;
use Stu\Orm\Entity\AnomalyInterface;

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
}
