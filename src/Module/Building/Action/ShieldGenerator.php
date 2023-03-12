<?php

declare(strict_types=1);

namespace Stu\Module\Building\Action;

use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Orm\Entity\ColonyInterface;

final class ShieldGenerator implements BuildingActionHandlerInterface
{
    private ColonyLibFactoryInterface $colonyLibFactory;

    public function __construct(
        ColonyLibFactoryInterface $colonyLibFactory
    ) {
        $this->colonyLibFactory = $colonyLibFactory;
    }

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
        $this->colonyLibFactory->createColonyShieldingManager($colony)->updateActualShields();
    }
}
