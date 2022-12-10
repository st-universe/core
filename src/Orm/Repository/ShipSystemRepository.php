<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\ShipSystem;
use Stu\Orm\Entity\ShipSystemInterface;

final class ShipSystemRepository extends EntityRepository implements ShipSystemRepositoryInterface
{

    public function prototype(): ShipSystemInterface
    {
        return new ShipSystem();
    }

    public function save(ShipSystemInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    public function delete(ShipSystemInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush();
    }

    public function getByShip(int $shipId): array
    {
        return $this->findBy(
            ['ship_id' => $shipId],
            ['system_type' => 'asc']
        );
    }

    public function truncateByShip(int $shipId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s s WHERE s.ship_id = :shipId',
                    ShipSystem::class
                )
            )
            ->setParameter('shipId', $shipId)
            ->execute();
    }
}
