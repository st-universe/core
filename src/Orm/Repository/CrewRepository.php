<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;

/**
 * @extends EntityRepository<Crew>
 */
final class CrewRepository extends EntityRepository implements CrewRepositoryInterface
{
    #[\Override]
    public function prototype(): Crew
    {
        return new Crew();
    }

    #[\Override]
    public function save(Crew $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    #[\Override]
    public function delete(Crew $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
    }

    #[\Override]
    public function getAmountByUserAndShipRumpCategory(
        User $user,
        SpacecraftRumpCategoryEnum $shipRumpCategory
    ): int {
        return (int) $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT COUNT(c.id) FROM %s c
                    JOIN %s ca
                    WITH ca.crew = c
                    JOIN %s sp
                    WITH ca.spacecraft = sp
                    JOIN %s r
                    WITH sp.rump = r
                    WHERE c.user = :user
                    AND r.category_id = :categoryId',
                    Crew::class,
                    CrewAssignment::class,
                    Spacecraft::class,
                    SpacecraftRump::class
                )
            )
            ->setParameters([
                'user' => $user,
                'categoryId' => $shipRumpCategory->value
            ])
            ->getSingleScalarResult();
    }

    #[\Override]
    public function truncateByUser(int $userId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s c WHERE c.user_id = :userId',
                    Crew::class
                )
            )
            ->setParameter('userId', $userId)
            ->execute();
    }
}
