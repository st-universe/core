<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\CrewInterface;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends EntityRepository<Crew>
 */
final class CrewRepository extends EntityRepository implements CrewRepositoryInterface
{
    #[Override]
    public function prototype(): CrewInterface
    {
        return new Crew();
    }

    #[Override]
    public function save(CrewInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    #[Override]
    public function delete(CrewInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
    }

    #[Override]
    public function getAmountByUserAndShipRumpCategory(
        UserInterface $user,
        int $shipRumpCategoryId
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
                'categoryId' => $shipRumpCategoryId
            ])
            ->getSingleScalarResult();
    }

    #[Override]
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

    #[Override]
    public function truncateAllCrew(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s c',
                Crew::class
            )
        )->execute();
    }
}
