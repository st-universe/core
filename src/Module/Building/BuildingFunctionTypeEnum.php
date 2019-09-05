<?php

declare(strict_types=1);

namespace Stu\Module\Building;

final class BuildingFunctionTypeEnum
{

    public static function getModuleFabOptions(): array
    {
        return [
            BUILDING_FUNCTION_MODULEFAB_TYPE1_LVL1,
            BUILDING_FUNCTION_MODULEFAB_TYPE1_LVL2,
            BUILDING_FUNCTION_MODULEFAB_TYPE1_LVL3,
            BUILDING_FUNCTION_MODULEFAB_TYPE2_LVL1,
            BUILDING_FUNCTION_MODULEFAB_TYPE2_LVL2,
            BUILDING_FUNCTION_MODULEFAB_TYPE2_LVL3,
            BUILDING_FUNCTION_MODULEFAB_TYPE3_LVL1,
            BUILDING_FUNCTION_MODULEFAB_TYPE3_LVL2,
            BUILDING_FUNCTION_MODULEFAB_TYPE3_LVL3,
        ];
    }

    public static function getShipyardOptions(): array
    {
        return [
            BUILDING_FUNCTION_ESCORT_SHIPYARD,
            BUILDING_FUNCTION_FRIGATE_SHIPYARD,
            BUILDING_FUNCTION_CRUISER_SHIPYARD,
            BUILDING_FUNCTION_DESTROYER_SHIPYARD
        ];
    }
}