<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipLog;

/**
 * @extends ObjectRepository<ShipLog>
 */
interface ShipLogRepositoryInterface extends ObjectRepository
{
    public function prototype(): ShipLog;

    public function save(ShipLog $shipLog): void;

    public function delete(ShipLog $shipLog): void;
}
