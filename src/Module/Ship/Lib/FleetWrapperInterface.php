<?php

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\FleetInterface;

interface FleetWrapperInterface
{
    public function get(): FleetInterface;

    /**
     * @return ShipWrapperInterface[]
     */
    public function getShipWrappers(): array;

    public function isForeignFleet(): bool;
}
