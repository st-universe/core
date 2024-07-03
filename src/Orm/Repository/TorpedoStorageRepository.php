<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\TorpedoStorage;
use Stu\Orm\Entity\TorpedoStorageInterface;

/**
 * @extends EntityRepository<TorpedoStorage>
 */
final class TorpedoStorageRepository extends EntityRepository implements TorpedoStorageRepositoryInterface
{
    #[Override]
    public function prototype(): TorpedoStorageInterface
    {
        return new TorpedoStorage();
    }

    #[Override]
    public function save(TorpedoStorageInterface $torpedoStorage): void
    {
        $em = $this->getEntityManager();

        $em->persist($torpedoStorage);
    }

    #[Override]
    public function delete(TorpedoStorageInterface $torpedoStorage): void
    {
        $em = $this->getEntityManager();

        $em->remove($torpedoStorage);
    }

    #[Override]
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
