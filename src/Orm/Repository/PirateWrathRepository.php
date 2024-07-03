<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Override;
use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\PirateWrath;
use Stu\Orm\Entity\PirateWrathInterface;
use Stu\Orm\Entity\User;

/**
 * @extends EntityRepository<PirateWrath>
 *
 * @method PirateWrathInterface[] findAll()
 */
final class PirateWrathRepository extends EntityRepository implements PirateWrathRepositoryInterface
{
    #[Override]
    public function save(PirateWrathInterface $wrath): void
    {
        $em = $this->getEntityManager();

        $em->persist($wrath);
    }

    #[Override]
    public function delete(PirateWrathInterface $wrath): void
    {
        $em = $this->getEntityManager();

        $em->remove($wrath);
    }

    #[Override]
    public function prototype(): PirateWrathInterface
    {
        return new PirateWrath();
    }

    #[Override]
    public function truncateAllEntries(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s pw',
                PirateWrath::class
            )
        )->execute();
    }

    /**
     * @return PirateWrathInterface[]
     */
    #[Override]
    public function getPirateWrathTop10(): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT pw
            FROM %s pw
            ORDER BY pw.wrath DESC',
                    PirateWrath::class
                )
            )
            ->setMaxResults(10)
            ->getResult();
    }
}
