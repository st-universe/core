<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Auxiliary;

use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

interface SpacecraftShutdownInterface
{
    public function shutdown(SpacecraftWrapperInterface $wrapper, bool $doLeaveFleet = false): void;
}
