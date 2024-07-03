<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\ShipRumpModuleLevel;
use Stu\Orm\Entity\ShipRumpModuleLevelInterface;

/**
 * @extends EntityRepository<ShipRumpModuleLevel>
 */
final class ShipRumpModuleLevelRepository extends EntityRepository implements ShipRumpModuleLevelRepositoryInterface
{
    #[Override]
    public function getByShipRump(int $shipRumpId): ?ShipRumpModuleLevelInterface
    {
        return $this->findOneBy([
            'rump_id' => $shipRumpId,
        ]);
    }
}
