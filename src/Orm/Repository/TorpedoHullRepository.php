<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\TorpedoHull;
use Stu\Orm\Entity\TorpedoHullInterface;

/**
 * @extends EntityRepository<TorpedoHull>
 */
final class TorpedoHullRepository extends EntityRepository implements TorpedoHullRepositoryInterface
{
    public function prototype(): TorpedoHullInterface
    {
        return new TorpedoHull();
    }

    public function save(TorpedoHullInterface $torpedohull): void
    {
        $em = $this->getEntityManager();

        $em->persist($torpedohull);
    }

    public function delete(TorpedoHullInterface $torpedohull): void
    {
        $em = $this->getEntityManager();

        $em->remove($torpedohull);
    }

    public function getModificatorMinAndMax(): array
    {
        $min =  (int)$this->getEntityManager()->createQuery(
            sprintf(
                'SELECT min(th.modificator) FROM %s th',
                TorpedoHull::class
            )
        )->getSingleScalarResult();

        $max =  (int)$this->getEntityManager()->createQuery(
            sprintf(
                'SELECT max(th.modificator) FROM %s th',
                TorpedoHull::class
            )
        )->getSingleScalarResult();

        return [$min, $max];
    }
}
