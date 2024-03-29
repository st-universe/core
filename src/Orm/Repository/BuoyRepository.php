<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\Buoy;
use Stu\Orm\Entity\BuoyInterface;


/**
 * @extends EntityRepository<Buoy>
 */
final class BuoyRepository extends EntityRepository implements BuoyRepositoryInterface
{
    public function prototype(): BuoyInterface
    {
        return new Buoy();
    }

    public function save(BuoyInterface $buoy): void
    {
        $em = $this->getEntityManager();

        $em->persist($buoy);
    }

    public function delete(BuoyInterface $buoy): void
    {
        $em = $this->getEntityManager();

        $em->remove($buoy);
    }

    public function findByMapId(int $mapId): array
    {
        return $this->findBy(['map_id' => $mapId]);
    }

    public function findBySysMapId(int $sysMapId): array
    {
        return $this->findBy(['sys_map_id' => $sysMapId]);
    }

    public function findByUserId(int $userId): array
    {
        return $this->findBy(['user_id' => $userId]);
    }
}