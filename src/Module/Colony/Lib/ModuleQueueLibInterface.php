<?php

namespace Stu\Module\Colony\Lib;

use Stu\Orm\Entity\BuildingInterface;
use Stu\Orm\Entity\ColonyInterface;

interface ModuleQueueLibInterface
{
    public function cancelModuleQueues(ColonyInterface $colony, BuildingInterface $building);
}
