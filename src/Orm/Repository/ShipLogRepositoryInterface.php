<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipLog;
use Stu\Orm\Entity\ShipLogInterface;

/**
 * @extends ObjectRepository<ShipLog>
 */
interface ShipLogRepositoryInterface extends ObjectRepository
{
    public function prototype(): ShipLogInterface;

    public function save(ShipLogInterface $shipLog): void;

    public function delete(ShipLogInterface $shipLog): void;
}
