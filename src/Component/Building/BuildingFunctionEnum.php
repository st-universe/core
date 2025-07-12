<?php

declare(strict_types=1);

namespace Stu\Component\Building;

enum BuildingFunctionEnum: int
{
    // building functions
    case COLONY_CENTRAL = 1;
    case BASE_CAMP = 2;
    case AIRFIELD = 4;
    case FIGHTER_SHIPYARD = 5;
    case ESCORT_SHIPYARD = 6;
    case FRIGATE_SHIPYARD = 7;
    case CRUISER_SHIPYARD = 8;
    case TORPEDO_FAB = 9;
    case MODULEFAB_TYPE1_LVL1 = 10;
    case MODULEFAB_TYPE1_LVL2 = 11;
    case MODULEFAB_TYPE1_LVL3 = 12;
    case MODULEFAB_TYPE2_LVL1 = 13;
    case MODULEFAB_TYPE2_LVL2 = 14;
    case MODULEFAB_TYPE2_LVL3 = 15;
    case MODULEFAB_TYPE3_LVL1 = 16;
    case MODULEFAB_TYPE3_LVL2 = 17;
    case MODULEFAB_TYPE3_LVL3 = 18;
    case ACADEMY = 20;
    case DESTROYER_SHIPYARD = 21;
    case REPAIR_SHIPYARD = 22;
    case WAREHOUSE = 23;
    case SUBSPACE_TELESCOPE = 31;

        // planetary defense
    case SHIELD_GENERATOR = 24;
    case SHIELD_BATTERY = 25;
    case ENERGY_PHALANX = 26;
    case PARTICLE_PHALANX = 27;
    case ANTI_PARTICLE = 28;

        // spare parts
    case FABRICATION_HALL = 29;
    case TECH_CENTER = 30;

    public function isModuleFab(): bool
    {
        return match ($this) {
            self::MODULEFAB_TYPE1_LVL1,
            self::MODULEFAB_TYPE1_LVL2,
            self::MODULEFAB_TYPE1_LVL3,
            self::MODULEFAB_TYPE2_LVL1,
            self::MODULEFAB_TYPE2_LVL2,
            self::MODULEFAB_TYPE2_LVL3,
            self::MODULEFAB_TYPE3_LVL1,
            self::MODULEFAB_TYPE3_LVL2,
            self::MODULEFAB_TYPE3_LVL3 => true,
            default => false
        };
    }

    public function isShipyard(): bool
    {
        return match ($this) {
            self::FIGHTER_SHIPYARD,
            self::ESCORT_SHIPYARD,
            self::FRIGATE_SHIPYARD,
            self::CRUISER_SHIPYARD,
            self::DESTROYER_SHIPYARD => true,
            default => false
        };
    }

    /** @return array<BuildingFunctionEnum> */
    public static function getModuleFabs(): array
    {
        return array_filter(
            self::cases(),
            fn(BuildingFunctionEnum $function): bool => $function->isModuleFab()
        );
    }

    /** @return array<BuildingFunctionEnum> */
    public static function getShipyards(): array
    {
        return array_filter(
            self::cases(),
            fn(BuildingFunctionEnum $function): bool => $function->isShipyard()
        );
    }
}
