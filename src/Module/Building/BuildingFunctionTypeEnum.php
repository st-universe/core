<?php

declare(strict_types=1);

namespace Stu\Module\Building;

use Stu\Component\Building\BuildingEnum;
use Stu\Component\Colony\ColonyMenuEnum;

final class BuildingFunctionTypeEnum
{
    /**
     * @return array<int>
     */
    public static function getModuleFabOptions(): array
    {
        return [
            BuildingEnum::BUILDING_FUNCTION_MODULEFAB_TYPE1_LVL1,
            BuildingEnum::BUILDING_FUNCTION_MODULEFAB_TYPE1_LVL2,
            BuildingEnum::BUILDING_FUNCTION_MODULEFAB_TYPE1_LVL3,
            BuildingEnum::BUILDING_FUNCTION_MODULEFAB_TYPE2_LVL1,
            BuildingEnum::BUILDING_FUNCTION_MODULEFAB_TYPE2_LVL2,
            BuildingEnum::BUILDING_FUNCTION_MODULEFAB_TYPE2_LVL3,
            BuildingEnum::BUILDING_FUNCTION_MODULEFAB_TYPE3_LVL1,
            BuildingEnum::BUILDING_FUNCTION_MODULEFAB_TYPE3_LVL2,
            BuildingEnum::BUILDING_FUNCTION_MODULEFAB_TYPE3_LVL3,
        ];
    }

    /**
     * @return array<int>
     */
    public static function getShipyardOptions(): array
    {
        return [
            BuildingEnum::BUILDING_FUNCTION_FIGHTER_SHIPYARD,
            BuildingEnum::BUILDING_FUNCTION_ESCORT_SHIPYARD,
            BuildingEnum::BUILDING_FUNCTION_FRIGATE_SHIPYARD,
            BuildingEnum::BUILDING_FUNCTION_CRUISER_SHIPYARD,
            BuildingEnum::BUILDING_FUNCTION_DESTROYER_SHIPYARD
        ];
    }

    public static function isBuildingFunctionMandatory(ColonyMenuEnum $menu): bool
    {
        switch ($menu) {
            case ColonyMenuEnum::MENU_SHIPYARD:
            case ColonyMenuEnum::MENU_BUILDPLANS:
                return true;
            default:
                return false;
        }
    }
}
