<?php

declare(strict_types=1);

namespace Stu\Module\Building\Action;

use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonySandbox;

final class ShieldGenerator implements BuildingActionHandlerInterface
{
    public function __construct(private ColonyLibFactoryInterface $colonyLibFactory) {}

    #[\Override]
    public function destruct(BuildingFunctionEnum $buildingFunction, Colony $colony): void
    {
        //nothing to do here
    }

    #[\Override]
    public function deactivate(BuildingFunctionEnum $buildingFunction, Colony|ColonySandbox $host): void
    {
        if ($host instanceof Colony) {
            $host->getChangeable()->setShields(0);
        }
    }

    #[\Override]
    public function activate(BuildingFunctionEnum $buildingFunction, Colony|ColonySandbox $host): void
    {
        if ($host instanceof Colony) {
            $this->colonyLibFactory->createColonyShieldingManager($host)->updateActualShields();
        }
    }
}
