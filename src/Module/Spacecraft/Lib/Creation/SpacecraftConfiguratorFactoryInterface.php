<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Creation;

use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

interface SpacecraftConfiguratorFactoryInterface
{
    /**
     * @template T of SpacecraftWrapperInterface
     *
     * @psalm-param T $wrapper
     *
     * @return SpacecraftConfiguratorInterface<T>
     */
    public function createSpacecraftConfigurator($wrapper): SpacecraftConfiguratorInterface;
}
