<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\ShipStorage;
use Stu\Orm\Entity\ShipStorageInterface;

final class ShipStorageRepository extends EntityRepository implements ShipStorageRepositoryInterface
{
    public function getByShip(int $shipId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT s FROM %s s INDEX BY s.goods_id LEFT JOIN %s g WITH g.id = s.goods_id WHERE s.ships_id = :shipId ORDER BY g.sort',
                    ShipStorage::class,
                    Commodity::class
                )
            )
            ->setParameters(['shipId' => $shipId])
            ->getResult();
    }

    public function prototype(): ShipStorageInterface
    {
        return new ShipStorage();
    }

    public function save(ShipStorageInterface $shipStorage): void
    {
        $em = $this->getEntityManager();

        $em->persist($shipStorage);
        $em->flush($shipStorage);
    }

    public function delete(ShipStorageInterface $shipStorage): void
    {
        $em = $this->getEntityManager();

        $em->remove($shipStorage);
        $em->flush($shipStorage);
    }

    public function truncateForShip(int $shipId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s s WHERE s.ships_id = :shipId',
                    ShipStorage::class
                )
            )
            ->setParameters(['shipId' => $shipId])
            ->execute();
    }
}