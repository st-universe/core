<?php

namespace Stu\Module\Spacecraft\Lib\Torpedo;

use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\TorpedoStorage;

interface ClearTorpedoInterface
{
    public function clearTorpedoStorage(SpacecraftWrapperInterface $wrapper, ?TorpedoStorage $torpedoStorage = null): void;
}
