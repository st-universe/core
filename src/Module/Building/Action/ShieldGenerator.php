<?php

declare(strict_types=1);

namespace Stu\Module\Building\Action;

use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ColonySandboxInterface;

final class ShieldGenerator implements BuildingActionHandlerInterface
{
    private ColonyLibFactoryInterface $colonyLibFactory;

    public function __construct(
        ColonyLibFactoryInterface $colonyLibFactory
    ) {
        $this->colonyLibFactory = $colonyLibFactory;
    }

    public function destruct(int $buildingFunctionId, ColonyInterface $colony): void
    {
        //nothing to do here
    }

    public function deactivate(int $buildingFunctionId, ColonyInterface|ColonySandboxInterface $host): void
    {
        if ($host instanceof ColonyInterface) {
            $host->setShields(0);
        }
    }

    public function activate(int $buildingFunctionId, ColonyInterface|ColonySandboxInterface $host): void
    {
        if ($host instanceof ColonyInterface) {
            $this->colonyLibFactory->createColonyShieldingManager($host)->updateActualShields();
        }
    }
}
