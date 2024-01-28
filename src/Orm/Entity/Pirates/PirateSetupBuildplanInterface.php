<?php

namespace Stu\Orm\Entity\Pirates;

use Stu\Orm\Entity\ShipBuildplanInterface;

interface PirateSetupBuildplanInterface
{
    public function getBuildplan(): ShipBuildplanInterface;

    public function getAmount(): int;
}
