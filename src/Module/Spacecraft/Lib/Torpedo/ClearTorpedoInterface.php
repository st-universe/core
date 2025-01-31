<?php

namespace Stu\Module\Spacecraft\Lib\Torpedo;

use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

interface ClearTorpedoInterface
{
    public function clearTorpedoStorage(SpacecraftWrapperInterface $wrapper): void;
}
