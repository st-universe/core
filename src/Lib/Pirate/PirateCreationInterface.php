<?php

namespace Stu\Lib\Pirate;

use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\ShipInterface;

interface PirateCreationInterface
{
    /** @return array<FleetInterface> */
    public function createPirateFleetsIfNeeded(): array;

    public function createPirateFleet(ShipInterface $supportCaller = null): FleetInterface;
}
