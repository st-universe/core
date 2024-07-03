<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\Buoy;
use Stu\Orm\Entity\BuoyInterface;

/**
 * @extends EntityRepository<Buoy>
 */
final class BuoyRepository extends EntityRepository implements BuoyRepositoryInterface
{
    #[Override]
    public function prototype(): BuoyInterface
    {
        return new Buoy();
    }

    #[Override]
    public function save(BuoyInterface $buoy): void
    {
        $em = $this->getEntityManager();

        $em->persist($buoy);
    }

    #[Override]
    public function delete(BuoyInterface $buoy): void
    {
        $em = $this->getEntityManager();

        $em->remove($buoy);
    }

    #[Override]
    public function findByUserId(int $userId): array
    {
        return $this->findBy(['user_id' => $userId]);
    }
}
