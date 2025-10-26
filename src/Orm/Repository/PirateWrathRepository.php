<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\PirateWrath;
use Stu\Orm\Entity\User;

/**
 * @extends EntityRepository<PirateWrath>
 *
 * @method PirateWrath[] findAll()
 */
final class PirateWrathRepository extends EntityRepository implements PirateWrathRepositoryInterface
{
    #[\Override]
    public function save(PirateWrath $wrath): void
    {
        $em = $this->getEntityManager();

        $em->persist($wrath);
    }

    #[\Override]
    public function delete(PirateWrath $wrath): void
    {
        $em = $this->getEntityManager();
        $em->remove($wrath);
        $em->flush();
    }

    #[\Override]
    public function prototype(): PirateWrath
    {
        return new PirateWrath();
    }

    /**
     * @return PirateWrath[]
     */
    #[\Override]
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

    /**
     * @return PirateWrath[]
     */
    #[\Override]
    public function getByUser(User $user): array
    {
        return $this->findBy(
            ['user' => $user]
        );
    }
}
