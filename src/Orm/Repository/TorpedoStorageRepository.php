<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\TorpedoStorage;
use Stu\Orm\Entity\TorpedoStorageInterface;

/**
 * @extends EntityRepository<TorpedoStorage>
 */
final class TorpedoStorageRepository extends EntityRepository implements TorpedoStorageRepositoryInterface
{
    public function prototype(): TorpedoStorageInterface
    {
        return new TorpedoStorage();
    }

    public function save(TorpedoStorageInterface $torpedoStorage): void
    {
        $em = $this->getEntityManager();

        $em->persist($torpedoStorage);
    }

    public function delete(TorpedoStorageInterface $torpedoStorage): void
    {
        $em = $this->getEntityManager();

        $em->remove($torpedoStorage);
    }

    public function truncateAllTorpedoStorages(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s ts',
                TorpedoStorage::class
            )
        )->execute();
    }
}
