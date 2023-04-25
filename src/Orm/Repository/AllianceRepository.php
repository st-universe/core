<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceInterface;

/**
 * @extends EntityRepository<Alliance>
 */
final class AllianceRepository extends EntityRepository implements AllianceRepositoryInterface
{
    public function prototype(): AllianceInterface
    {
        return new Alliance();
    }

    public function save(AllianceInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    public function delete(AllianceInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
    }

    public function findAllOrdered(): array
    {
        return $this->findBy(
            [],
            ['id' => 'asc']
        );
    }

    public function findByApplicationState(bool $acceptApplications): array
    {
        return $this->findBy(
            ['accept_applications' => $acceptApplications],
            ['id' => 'asc']
        );
    }
}
