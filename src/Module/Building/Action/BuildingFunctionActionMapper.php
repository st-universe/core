<?php

declare(strict_types=1);

namespace Stu\Module\Building\Action;

use Override;
use Psr\Container\ContainerInterface;
use Stu\Component\Building\BuildingFunctionEnum;

final class BuildingFunctionActionMapper implements BuildingFunctionActionMapperInterface
{
    public function __construct(private ContainerInterface $container) {}

    #[Override]
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
            BuildingFunctionEnum::BUILDING_FUNCTION_ACADEMY => Academy::class,
            //SHIPYARDS
            BuildingFunctionEnum::BUILDING_FUNCTION_FIGHTER_SHIPYARD,
            BuildingFunctionEnum::BUILDING_FUNCTION_ESCORT_SHIPYARD,
            BuildingFunctionEnum::BUILDING_FUNCTION_FRIGATE_SHIPYARD,
            BuildingFunctionEnum::BUILDING_FUNCTION_CRUISER_SHIPYARD,
            BuildingFunctionEnum::BUILDING_FUNCTION_DESTROYER_SHIPYARD,
            //SHIELDS
            BuildingFunctionEnum::BUILDING_FUNCTION_SHIELD_BATTERY => ShieldBattery::class,
            BuildingFunctionEnum::BUILDING_FUNCTION_SHIELD_GENERATOR => ShieldGenerator::class,
            //MODULE FABS
            BuildingFunctionEnum::BUILDING_FUNCTION_MODULEFAB_TYPE1_LVL1,
            BuildingFunctionEnum::BUILDING_FUNCTION_MODULEFAB_TYPE1_LVL2,
            BuildingFunctionEnum::BUILDING_FUNCTION_MODULEFAB_TYPE1_LVL3,
            BuildingFunctionEnum::BUILDING_FUNCTION_MODULEFAB_TYPE2_LVL1,
            BuildingFunctionEnum::BUILDING_FUNCTION_MODULEFAB_TYPE2_LVL2,
            BuildingFunctionEnum::BUILDING_FUNCTION_MODULEFAB_TYPE2_LVL3,
            BuildingFunctionEnum::BUILDING_FUNCTION_MODULEFAB_TYPE3_LVL1,
            BuildingFunctionEnum::BUILDING_FUNCTION_MODULEFAB_TYPE3_LVL2,
            BuildingFunctionEnum::BUILDING_FUNCTION_MODULEFAB_TYPE3_LVL3  => ModuleFab::class,
            default => null
        };
    }
}
