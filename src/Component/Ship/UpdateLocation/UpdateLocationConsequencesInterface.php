<?php

namespace Stu\Component\Ship\UpdateLocation;

use Stu\Orm\Entity\ShipInterface;

interface UpdateLocationConsequencesInterface
{
    public function updateLocationWithConsequences(
        ShipInterface $ship,
        ?ShipInterface $tractoringShip,
        $nextField,
        array &$messages = null
    ): void;
}
