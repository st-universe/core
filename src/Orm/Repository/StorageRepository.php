<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\StorageInterface;

final class StorageRepository extends EntityRepository implements StorageRepositoryInterface
{
    public function prototype(): StorageInterface
    {
        return new Storage();
    }

    public function save(StorageInterface $storage): void
    {
        $em = $this->getEntityManager();

        $em->persist($storage);
    }

    public function delete(StorageInterface $storage): void
    {
        $em = $this->getEntityManager();

        $em->remove($storage);
    }
}
