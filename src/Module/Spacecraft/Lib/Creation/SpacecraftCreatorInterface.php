<?php

namespace Stu\Module\Spacecraft\Lib\Creation;

use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

/**
 * @template T of SpacecraftWrapperInterface
 */
interface SpacecraftCreatorInterface
{
    /** @return SpacecraftConfiguratorInterface<T> */
    public function createBy(
        int $userId,
        int $rumpId,
        int $buildplanId,
        SpecialSystemsProviderInterface $specialSystemsProvider
    ): SpacecraftConfiguratorInterface;
}
