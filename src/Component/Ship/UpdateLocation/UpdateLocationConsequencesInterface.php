<?php

namespace Stu\Component\Ship\UpdateLocation;

use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

interface UpdateLocationConsequencesInterface
{
    public function updateLocationWithConsequences(
        ShipWrapperInterface $wrapper,
        ?ShipInterface $tractoringShip,
        $nextField,
        array &$messages = null
    ): void;
}
