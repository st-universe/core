<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\LocationMining;
use Stu\Orm\Entity\LocationMiningInterface;

/**
 * @extends EntityRepository<LocationMining>
 */
final class LocationMiningRepository extends EntityRepository implements LocationMiningRepositoryInterface
{
    public function prototype(): LocationMiningInterface
    {
        return new LocationMining();
    }

    public function save(LocationMiningInterface $locationMining): void
    {
        $em = $this->getEntityManager();

        $em->persist($locationMining);
        $em->flush();
    }
}
