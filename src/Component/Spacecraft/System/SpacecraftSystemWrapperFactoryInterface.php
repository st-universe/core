<?php

namespace Stu\Component\Spacecraft\System;

use Stu\Orm\Entity\Spacecraft;

interface SpacecraftSystemWrapperFactoryInterface
{
    public function create(Spacecraft $spacecraft, SpacecraftSystemTypeEnum $type): ?SpacecraftSystemWrapper;
}
