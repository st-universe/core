<?php

namespace Stu\Orm\Entity;

interface PirateSetupBuildplanInterface
{
    public function getBuildplan(): ShipBuildplanInterface;

    public function getAmount(): int;
}
