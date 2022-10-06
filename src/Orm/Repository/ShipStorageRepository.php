<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\ShipStorage;
use Stu\Orm\Entity\ShipStorageInterface;

final class ShipStorageRepository extends EntityRepository implements ShipStorageRepositoryInterface
{
    public function prototype(): ShipStorageInterface
    {
        return new ShipStorage();
    }

    public function save(ShipStorageInterface $shipStorage): void
    {
        $em = $this->getEntityManager();

        $em->persist($shipStorage);
    }

    public function delete(ShipStorageInterface $shipStorage): void
    {
        $em = $this->getEntityManager();

        $em->remove($shipStorage);
    }
}
