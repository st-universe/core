<?php

namespace Stu\Module\Ship\Lib;

use Doctrine\Common\Collections\Collection;
use Stu\Orm\Entity\Fleet;

interface FleetWrapperInterface
{
    public function get(): Fleet;

    public function getLeadWrapper(): ShipWrapperInterface;

    /**
     * @return Collection<int, ShipWrapperInterface>
     */
    public function getShipWrappers(): Collection;

    public function isForeignFleet(): bool;
}
