<?php

namespace Stu\Lib\Pirate;

use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\Spacecraft;

interface PirateCreationInterface
{
    /** @return array<Fleet> */
    public function createPirateFleetsIfNeeded(): array;

    public function createPirateFleet(?Spacecraft $supportCaller = null): Fleet;
}
