<?php

namespace Stu\Orm\Entity;

use Stu\Orm\Entity\ShipBuildplanInterface;

interface PirateSetupBuildplanInterface
{
    public function getBuildplan(): ShipBuildplanInterface;

    public function getAmount(): int;
}
