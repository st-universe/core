<?php

namespace Stu\Module\Spacecraft\Lib\Reactor;

use Stu\Module\Spacecraft\Lib\ReactorWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapper;

interface ReactorWrapperFactoryInterface
{
    public function createReactorWrapper(SpacecraftWrapper $wrapper): ?ReactorWrapperInterface;
}
