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
}
