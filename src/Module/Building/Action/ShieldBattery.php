<?php

declare(strict_types=1);

namespace Stu\Module\Building\Action;

use Override;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ColonySandboxInterface;

final class ShieldBattery implements BuildingActionHandlerInterface
{
    public function __construct(private ColonyLibFactoryInterface $colonyLibFactory) {}

    #[Override]
    public function destruct(BuildingFunctionEnum $buildingFunction, ColonyInterface $colony): void
    {
        //nothing to do here
    }

    #[Override]
    public function deactivate(BuildingFunctionEnum $buildingFunction, ColonyInterface|ColonySandboxInterface $host): void
    {
        if ($host instanceof ColonyInterface) {
            $this->colonyLibFactory->createColonyShieldingManager($host)->updateActualShields();
        }
    }

    #[Override]
    public function activate(BuildingFunctionEnum $buildingFunction, ColonyInterface|ColonySandboxInterface $host): void
    {
        $this->colonyLibFactory->createColonyShieldingManager($host)->updateActualShields();
    }
}
