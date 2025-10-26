<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\TorpedoStorage;

/**
 * @extends EntityRepository<TorpedoStorage>
 */
final class TorpedoStorageRepository extends EntityRepository implements TorpedoStorageRepositoryInterface
{
    #[\Override]
    public function prototype(): TorpedoStorage
    {
        return new TorpedoStorage();
    }

    #[\Override]
    public function save(TorpedoStorage $torpedoStorage): void
    {
        $em = $this->getEntityManager();

        $em->persist($torpedoStorage);
    }

    #[\Override]
    public function delete(TorpedoStorage $torpedoStorage): void
    {
        $em = $this->getEntityManager();

        $em->remove($torpedoStorage);
    }
}
