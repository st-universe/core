<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\ShipLog;
use Stu\Orm\Entity\ShipLogInterface;

/**
 * @extends EntityRepository<ShipLog>
 */
final class ShipLogRepository extends EntityRepository implements ShipLogRepositoryInterface
{
    public function prototype(): ShipLogInterface
    {
        return new ShipLog();
    }

    public function save(ShipLogInterface $shipLog): void
    {
        $em = $this->getEntityManager();

        $em->persist($shipLog);
    }

    public function delete(ShipLogInterface $shipLog): void
    {
        $em = $this->getEntityManager();

        $em->remove($shipLog);
    }
}
