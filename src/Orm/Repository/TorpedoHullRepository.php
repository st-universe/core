<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\TorpedoHull;

/**
 * @extends EntityRepository<TorpedoHull>
 */
final class TorpedoHullRepository extends EntityRepository implements TorpedoHullRepositoryInterface
{
    #[Override]
    public function prototype(): TorpedoHull
    {
        return new TorpedoHull();
    }

    #[Override]
    public function save(TorpedoHull $torpedohull): void
    {
        $em = $this->getEntityManager();

        $em->persist($torpedohull);
    }

    #[Override]
    public function delete(TorpedoHull $torpedohull): void
    {
        $em = $this->getEntityManager();

        $em->remove($torpedohull);
    }

    #[Override]
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
