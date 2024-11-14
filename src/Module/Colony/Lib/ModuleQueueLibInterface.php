<?php

namespace Stu\Module\Colony\Lib;

use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Orm\Entity\BuildingInterface;
use Stu\Orm\Entity\ColonyInterface;

interface ModuleQueueLibInterface
{
    public function cancelModuleQueues(ColonyInterface $colony, BuildingFunctionEnum $buildingFunction);

    public function cancelModuleQueuesForBuilding(ColonyInterface $colony, BuildingInterface $building);
}
