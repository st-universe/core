<?php

namespace Stu\Module\Station\Lib\Creation;

use Stu\Module\Spacecraft\Lib\Creation\SpacecraftConfiguratorInterface;
use Stu\Module\Station\Lib\StationWrapperInterface;
use Stu\Orm\Entity\ConstructionProgress;

interface StationCreatorInterface
{
    /**
     * @return SpacecraftConfiguratorInterface<StationWrapperInterface>
     */
    public function createBy(
        int $userId,
        int $rumpId,
        int $buildplanId,
        ConstructionProgress $progress
    ): SpacecraftConfiguratorInterface;
}
