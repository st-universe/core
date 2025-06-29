<?php

namespace Stu\Module\Spacecraft\Lib\Reactor;

use Stu\Module\Spacecraft\Lib\ReactorWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapper;
use Stu\Orm\Entity\Spacecraft;

interface ReactorWrapperFactoryInterface
{
    /**
     * @template T of Spacecraft
     * @param SpacecraftWrapper<T> $wrapper
     */
    public function createReactorWrapper(SpacecraftWrapper $wrapper): ?ReactorWrapperInterface;
}
