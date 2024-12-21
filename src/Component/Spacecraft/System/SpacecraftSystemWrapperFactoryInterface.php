<?php

namespace Stu\Component\Spacecraft\System;

use Stu\Orm\Entity\SpacecraftInterface;

interface SpacecraftSystemWrapperFactoryInterface
{
    public function create(SpacecraftInterface $spacecraft, SpacecraftSystemTypeEnum $type): ?SpacecraftSystemWrapper;
}
