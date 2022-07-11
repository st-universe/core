<?php

namespace Stu\Module\Ship\Lib;

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
