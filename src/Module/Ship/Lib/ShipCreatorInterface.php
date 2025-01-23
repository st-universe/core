<?php

namespace Stu\Module\Ship\Lib;

use Stu\Module\Spacecraft\Lib\Creation\SpacecraftConfiguratorInterface;

interface ShipCreatorInterface
{
    /**
     * @return SpacecraftConfiguratorInterface<ShipWrapperInterface>
     */
    public function createBy(
        int $userId,
        int $rumpId,
        int $buildplanId
    ): SpacecraftConfiguratorInterface;
}
