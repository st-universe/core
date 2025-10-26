<?php

declare(strict_types=1);

namespace Stu\Module\Building\Action;

use Psr\Container\ContainerInterface;
use Stu\Component\Building\BuildingFunctionEnum;

final class BuildingFunctionActionMapper implements BuildingFunctionActionMapperInterface
{
    public function __construct(private ContainerInterface $container) {}

    #[\Override]
    public function map(BuildingFunctionEnum $buildingFunction): ?BuildingActionHandlerInterface
    {
        $handler = $this->getBuildingActionHandlerClass($buildingFunction);
        if ($handler === null) {
            return null;
        }

        return $this->container->get($handler);
    }

    private function getBuildingActionHandlerClass(BuildingFunctionEnum $buildingFunction): ?string
    {
        return match ($buildingFunction) {
            BuildingFunctionEnum::ACADEMY => Academy::class,
            //SHIPYARDS
            BuildingFunctionEnum::FIGHTER_SHIPYARD,
            BuildingFunctionEnum::ESCORT_SHIPYARD,
            BuildingFunctionEnum::FRIGATE_SHIPYARD,
            BuildingFunctionEnum::CRUISER_SHIPYARD,
            BuildingFunctionEnum::DESTROYER_SHIPYARD,
            //SHIELDS
            BuildingFunctionEnum::SHIELD_BATTERY => ShieldBattery::class,
            BuildingFunctionEnum::SHIELD_GENERATOR => ShieldGenerator::class,
            //MODULE FABS
            BuildingFunctionEnum::MODULEFAB_TYPE1_LVL1,
            BuildingFunctionEnum::MODULEFAB_TYPE1_LVL2,
            BuildingFunctionEnum::MODULEFAB_TYPE1_LVL3,
            BuildingFunctionEnum::MODULEFAB_TYPE2_LVL1,
            BuildingFunctionEnum::MODULEFAB_TYPE2_LVL2,
            BuildingFunctionEnum::MODULEFAB_TYPE2_LVL3,
            BuildingFunctionEnum::MODULEFAB_TYPE3_LVL1,
            BuildingFunctionEnum::MODULEFAB_TYPE3_LVL2,
            BuildingFunctionEnum::MODULEFAB_TYPE3_LVL3  => ModuleFab::class,
            default => null
        };
    }
}
