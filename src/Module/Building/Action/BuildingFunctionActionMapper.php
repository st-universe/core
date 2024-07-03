<?php

declare(strict_types=1);

namespace Stu\Module\Building\Action;

use Override;
use Psr\Container\ContainerInterface;
use Stu\Component\Building\BuildingEnum;

final class BuildingFunctionActionMapper implements BuildingFunctionActionMapperInterface
{
    public function __construct(private ContainerInterface $container)
    {
    }

    #[Override]
    public function map(int $buildingFunctionId): ?BuildingActionHandlerInterface
    {
        $map = [
            //SHIPYARDS
            BuildingEnum::BUILDING_FUNCTION_ACADEMY => Academy::class,
            BuildingEnum::BUILDING_FUNCTION_FIGHTER_SHIPYARD => Shipyard::class,
            BuildingEnum::BUILDING_FUNCTION_ESCORT_SHIPYARD => Shipyard::class,
            BuildingEnum::BUILDING_FUNCTION_FRIGATE_SHIPYARD => Shipyard::class,
            BuildingEnum::BUILDING_FUNCTION_CRUISER_SHIPYARD => Shipyard::class,
            BuildingEnum::BUILDING_FUNCTION_DESTROYER_SHIPYARD => Shipyard::class,

            //SHIELDS
            BuildingEnum::BUILDING_FUNCTION_SHIELD_BATTERY => ShieldBattery::class,
            BuildingEnum::BUILDING_FUNCTION_SHIELD_GENERATOR => ShieldGenerator::class,

            //MODULE FABS
            BuildingEnum::BUILDING_FUNCTION_MODULEFAB_TYPE1_LVL1 => ModuleFab::class,
            BuildingEnum::BUILDING_FUNCTION_MODULEFAB_TYPE1_LVL2 => ModuleFab::class,
            BuildingEnum::BUILDING_FUNCTION_MODULEFAB_TYPE1_LVL3 => ModuleFab::class,
            BuildingEnum::BUILDING_FUNCTION_MODULEFAB_TYPE2_LVL1 => ModuleFab::class,
            BuildingEnum::BUILDING_FUNCTION_MODULEFAB_TYPE2_LVL2 => ModuleFab::class,
            BuildingEnum::BUILDING_FUNCTION_MODULEFAB_TYPE2_LVL3 => ModuleFab::class,
            BuildingEnum::BUILDING_FUNCTION_MODULEFAB_TYPE3_LVL1 => ModuleFab::class,
            BuildingEnum::BUILDING_FUNCTION_MODULEFAB_TYPE3_LVL2 => ModuleFab::class,
            BuildingEnum::BUILDING_FUNCTION_MODULEFAB_TYPE3_LVL3  => ModuleFab::class
        ];

        $handler = $map[$buildingFunctionId] ?? null;

        if ($handler === null) {
            return null;
        }

        return $this->container->get($handler);
    }
}
