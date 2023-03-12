<?php

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\ShipInterface;

interface ShipRemoverInterface
{

    /**
     * Destroys a ship and replaces it by a nice debrisfield,
     * also starts escape pods if present
     */
    public function destroy(ShipWrapperInterface $wrapper): ?string;

    /**
     * Actually removes the ship entity including all references
     */
    public function remove(ShipInterface $ship, ?bool $truncateCrew = false): void;
}
