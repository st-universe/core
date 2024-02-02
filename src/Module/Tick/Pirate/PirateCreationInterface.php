<?php

namespace Stu\Module\Tick\Pirate;

use Stu\Orm\Entity\FleetInterface;

interface PirateCreationInterface
{
    /** @return array<FleetInterface> */
    public function createPirateFleetsIfNeeded(): array;
}
