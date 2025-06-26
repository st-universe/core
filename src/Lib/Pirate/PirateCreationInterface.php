<?php

namespace Stu\Lib\Pirate;

use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\Ship;

interface PirateCreationInterface
{
    /** @return array<Fleet> */
    public function createPirateFleetsIfNeeded(): array;

    public function createPirateFleet(?Ship $supportCaller = null): Fleet;
}
