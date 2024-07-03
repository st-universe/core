<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Override;
use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceInterface;

/**
 * @extends EntityRepository<Alliance>
 */
final class AllianceRepository extends EntityRepository implements AllianceRepositoryInterface
{
    #[Override]
    public function prototype(): AllianceInterface
    {
        return new Alliance();
    }

    #[Override]
    public function save(AllianceInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    #[Override]
    public function delete(AllianceInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
    }

    #[Override]
    public function findAllOrdered(): array
    {
        return $this->findBy(
            [],
            ['id' => 'asc']
        );
    }

    #[Override]
    public function findByApplicationState(bool $acceptApplications): array
    {
        return $this->findBy(
            ['accept_applications' => $acceptApplications],
            ['id' => 'asc']
        );
    }

    #[Override]
    public function truncateAllAlliances(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s a',
                Alliance::class
            )
        )->execute();
    }
}
