<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\PirateNames;
use Stu\Orm\Entity\PirateNamesInterface;

/**
 * @extends EntityRepository<PirateNames>
 */
final class PirateNamesRepository extends EntityRepository implements PirateNamesRepositoryInterface
{
    public function prototype(): PirateNamesInterface
    {
        return new PirateNames();
    }

    public function save(PirateNamesInterface $pirateName): void
    {
        $em = $this->getEntityManager();

        $em->persist($pirateName);
        $em->flush();
    }

    public function delete(PirateNamesInterface $pirateName): void
    {
        $em = $this->getEntityManager();

        $em->remove($pirateName);
        $em->flush();
    }

    public function mostUnusedNames(): array
    {
        $query = $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT pn
                 FROM %s pn
                 WHERE pn.count = (
                     SELECT MIN(pn2.count)
                     FROM %s pn2
                 )',
                PirateNames::class,
                PirateNames::class
            )
        );

        return $query->getResult();
    }
}
