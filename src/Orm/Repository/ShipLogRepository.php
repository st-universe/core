<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\ShipLog;

/**
 * @extends EntityRepository<ShipLog>
 */
final class ShipLogRepository extends EntityRepository implements ShipLogRepositoryInterface
{
    #[Override]
    public function prototype(): ShipLog
    {
        return new ShipLog();
    }

    #[Override]
    public function save(ShipLog $shipLog): void
    {
        $em = $this->getEntityManager();

        $em->persist($shipLog);
    }

    #[Override]
    public function delete(ShipLog $shipLog): void
    {
        $em = $this->getEntityManager();

        $em->remove($shipLog);
    }
}
