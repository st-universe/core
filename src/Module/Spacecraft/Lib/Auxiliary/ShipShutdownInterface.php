<?php

namespace Stu\Module\Spacecraft\Lib\Auxiliary;

use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

interface ShipShutdownInterface
{
    public function shutdown(SpacecraftWrapperInterface $wrapper, bool $doLeaveFleet = false): void;
}
