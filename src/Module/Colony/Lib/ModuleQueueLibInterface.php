<?php

namespace Stu\Module\Colony\Lib;

use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\Colony;

interface ModuleQueueLibInterface
{
    public function cancelModuleQueues(Colony $colony, BuildingFunctionEnum $buildingFunction): void;

    public function cancelModuleQueuesForBuilding(Colony $colony, Building $building): void;
}
