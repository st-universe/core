<?php

namespace Stu\Lib\Pirate;

use Stu\Orm\Entity\FleetInterface;

interface PirateCreationInterface
{
    /** @return array<FleetInterface> */
    public function createPirateFleetsIfNeeded(): array;
}
