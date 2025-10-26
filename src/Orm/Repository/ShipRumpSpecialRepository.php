<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\ShipRumpSpecial;

/**
 * @extends EntityRepository<ShipRumpSpecial>
 */
final class ShipRumpSpecialRepository extends EntityRepository implements ShipRumpSpecialRepositoryInterface
{
    #[\Override]
    public function getByShipRump(int $rumpId): array
    {
        return $this->findBy([
            'rump_id' => $rumpId
        ]);
    }
}
