<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ColonyStorage;
use Stu\Orm\Entity\ColonyStorageInterface;

final class ColonyStorageRepository extends EntityRepository implements ColonyStorageRepositoryInterface
{


    public function prototype(): ColonyStorageInterface
    {
        return new ColonyStorage();
    }

    public function save(ColonyStorageInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    public function delete(ColonyStorageInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
    }

    public function truncateByColony(ColonyInterface $colony): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s cs WHERE cs.colonies_id = :colony',
                    ColonyStorage::class
                )
            )
            ->setParameter('colony', $colony)
            ->execute();
    }
}
