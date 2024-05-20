<?php

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\ShipInterface;

interface ShipRemoverInterface
{
    /**
     * Actually removes the ship entity including all references
     */
    public function remove(ShipInterface $ship, ?bool $truncateCrew = false): void;
}
