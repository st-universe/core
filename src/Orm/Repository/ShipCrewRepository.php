<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\ShipCrew;
use Stu\Orm\Entity\ShipCrewInterface;

final class ShipCrewRepository extends EntityRepository implements ShipCrewRepositoryInterface
{
    public function prototype(): ShipCrewInterface
    {
        return new ShipCrew();
    }

    public function save(ShipCrewInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    public function delete(ShipCrewInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        //$em->flush();
    }

    public function getByShip(int $shipId): array
    {
        return $this->findBy(
            ['ships_id' => $shipId],
            ['slot' => 'asc']
        );
    }

    public function getByShipAndSlot(int $shipId, int $slotId): array
    {
        return $this->findBy([
            'ships_id' => $shipId,
            'slot' => $slotId
        ]);
    }

    public function getAmountByShip(int $shipId): int
    {
        return $this->count([
            'ships_id' => $shipId
        ]);
    }

    public function getAmountByUser(int $userId): int
    {
        return $this->count([
            'user_id' => $userId
        ]);
    }

    public function truncateByShip(int $shipId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s sc WHERE sc.ships_id = :shipId',
                    ShipCrew::class
                )
            )
            ->setParameter('shipId', $shipId)
            ->execute();
    }
}
