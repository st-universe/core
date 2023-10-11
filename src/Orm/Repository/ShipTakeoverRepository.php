<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\ShipTakeover;
use Stu\Orm\Entity\ShipTakeoverInterface;

/**
 * @extends EntityRepository<ShipTakeover>
 */
final class ShipTakeoverRepository extends EntityRepository implements ShipTakeoverRepositoryInterface
{
    public function prototype(): ShipTakeoverInterface
    {
        return new ShipTakeover();
    }

    public function save(ShipTakeoverInterface $shipTakeover): void
    {
        $em = $this->getEntityManager();

        $em->persist($shipTakeover);
    }

    public function delete(ShipTakeoverInterface $shipTakeover): void
    {
        $em = $this->getEntityManager();

        $em->remove($shipTakeover);
    }
}
