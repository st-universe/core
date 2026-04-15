<?php

declare(strict_types=1);

namespace Stu\Module\Building\Action;

use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonySandbox;
use Stu\Orm\Entity\PlanetField;

final class ShieldBattery implements BuildingActionHandlerInterface
{
    public function __construct(private ColonyLibFactoryInterface $colonyLibFactory) {}

    #[\Override]
    public function destruct(BuildingFunctionEnum $buildingFunction, Colony $colony): void
    {
        //nothing to do here
    }

    #[\Override]
    public function deactivate(BuildingFunctionEnum $buildingFunction, Colony|ColonySandbox $host, ?PlanetField $field = null): void
    {
        if ($host instanceof Colony) {
            $this->colonyLibFactory->createColonyShieldingManager($host)->updateActualShields();
        }
    }

    #[\Override]
    public function activate(BuildingFunctionEnum $buildingFunction, Colony|ColonySandbox $host, ?PlanetField $field = null): void
    {
        $this->colonyLibFactory->createColonyShieldingManager($host)->updateActualShields();
    }
}
