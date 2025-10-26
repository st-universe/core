<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\Buoy;

/**
 * @extends EntityRepository<Buoy>
 */
final class BuoyRepository extends EntityRepository implements BuoyRepositoryInterface
{
    #[\Override]
    public function prototype(): Buoy
    {
        return new Buoy();
    }

    #[\Override]
    public function save(Buoy $buoy): void
    {
        $em = $this->getEntityManager();

        $em->persist($buoy);
    }

    #[\Override]
    public function delete(Buoy $buoy): void
    {
        $em = $this->getEntityManager();

        $em->remove($buoy);
    }

    #[\Override]
    public function findByUserId(int $userId): array
    {
        return $this->findBy(['user_id' => $userId]);
    }
}
