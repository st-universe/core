<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Override;
use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\CrewInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\ShipCrew;
use Stu\Orm\Entity\ShipRump;
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
                    'SELECT COUNT(c.id) FROM %s c WHERE c.user = :user AND c.id IN (
                        SELECT ship.crew_id FROM %s ship WHERE ship.ship_id IN (
                            SELECT rump.id FROM %s rump WHERE rump.rumps_id IN (
                                SELECT rump_category.id FROM %s rump_category WHERE rump_category.category_id = :categoryId
                            )
                        )
                    )',
                    Crew::class,
                    ShipCrew::class,
                    Ship::class,
                    ShipRump::class
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
