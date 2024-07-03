<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Override;
use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\ShipTakeover;
use Stu\Orm\Entity\ShipTakeoverInterface;

/**
 * @extends EntityRepository<ShipTakeover>
 */
final class ShipTakeoverRepository extends EntityRepository implements ShipTakeoverRepositoryInterface
{
    #[Override]
    public function prototype(): ShipTakeoverInterface
    {
        return new ShipTakeover();
    }

    #[Override]
    public function save(ShipTakeoverInterface $shipTakeover): void
    {
        $em = $this->getEntityManager();

        $em->persist($shipTakeover);
    }

    #[Override]
    public function delete(ShipTakeoverInterface $shipTakeover): void
    {
        $em = $this->getEntityManager();

        $em->remove($shipTakeover);
    }
}
