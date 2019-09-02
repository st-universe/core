<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;

final class ShipRumpSpecialRepository extends EntityRepository implements ShipRumpSpecialRepositoryInterface
{
    public function getByShipRump(int $shipRumpId): array
    {
        return $this->findBy([
            'rumps_id' => $shipRumpId
        ]);
    }
}