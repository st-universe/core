<?php

declare(strict_types=1);

namespace Stu\Module\Building\Action;

use Stu\Component\Colony\Shields\ColonyShieldsManager;
use Stu\Orm\Entity\ColonyInterface;

final class ShieldGenerator implements BuildingActionHandlerInterface
{
    public function destruct(int $buildingFunctionId, int $colonyId): void
    {
        //nothing to do here
    }

    public function deactivate(int $buildingFunctionId, ColonyInterface $colony): void
    {
        $colony->setShields(0);
    }

    public function activate(int $buildingFunctionId, ColonyInterface $colony): void
    {
        ColonyShieldsManager::updateActualShields($colony);
    }
}
