<?php

declare(strict_types=1);

namespace Stu\Component\Building;

final class BuildingEnum
{
    // building functions
    /**
     * @var int
     */
    public const BUILDING_FUNCTION_CENTRAL = 1;

    /**
     * @var int
     */
    public const BUILDING_FUNCTION_AIRFIELD = 4;

    /**
     * @var int
     */
    public const BUILDING_FUNCTION_FIGHTER_SHIPYARD = 5;

    /**
     * @var int
     */
    public const BUILDING_FUNCTION_ESCORT_SHIPYARD = 6;

    /**
     * @var int
     */
    public const BUILDING_FUNCTION_FRIGATE_SHIPYARD = 7;

    /**
     * @var int
     */
    public const BUILDING_FUNCTION_CRUISER_SHIPYARD = 8;

    /**
     * @var int
     */
    public const BUILDING_FUNCTION_TORPEDO_FAB = 9;

    /**
     * @var int
     */
    public const BUILDING_FUNCTION_MODULEFAB_TYPE1_LVL1 = 10;

    /**
     * @var int
     */
    public const BUILDING_FUNCTION_MODULEFAB_TYPE1_LVL2 = 11;

    /**
     * @var int
     */
    public const BUILDING_FUNCTION_MODULEFAB_TYPE1_LVL3 = 12;

    /**
     * @var int
     */
    public const BUILDING_FUNCTION_MODULEFAB_TYPE2_LVL1 = 13;

    /**
     * @var int
     */
    public const BUILDING_FUNCTION_MODULEFAB_TYPE2_LVL2 = 14;

    /**
     * @var int
     */
    public const BUILDING_FUNCTION_MODULEFAB_TYPE2_LVL3 = 15;

    /**
     * @var int
     */
    public const BUILDING_FUNCTION_MODULEFAB_TYPE3_LVL1 = 16;

    /**
     * @var int
     */
    public const BUILDING_FUNCTION_MODULEFAB_TYPE3_LVL2 = 17;

    /**
     * @var int
     */
    public const BUILDING_FUNCTION_MODULEFAB_TYPE3_LVL3 = 18;

    /**
     * @var int
     */
    public const BUILDING_FUNCTION_ACADEMY = 20;

    /**
     * @var int
     */
    public const BUILDING_FUNCTION_DESTROYER_SHIPYARD = 21;

    /**
     * @var int
     */
    public const BUILDING_FUNCTION_REPAIR_SHIPYARD = 22;

    /**
     * @var int
     */
    public const BUILDING_FUNCTION_WAREHOUSE = 23;

    /**
     * @var int
     */
    public const BUILDING_FUNCTION_SUBSPACE_TELESCOPE = 31;

    // planetary defense
    /**
     * @var int
     */
    public const BUILDING_FUNCTION_SHIELD_GENERATOR = 24;

    /**
     * @var int
     */
    public const BUILDING_FUNCTION_SHIELD_BATTERY = 25;

    /**
     * @var int
     */
    public const BUILDING_FUNCTION_ENERGY_PHALANX = 26;

    /**
     * @var int
     */
    public const BUILDING_FUNCTION_PARTICLE_PHALANX = 27;

    /**
     * @var int
     */
    public const BUILDING_FUNCTION_ANTI_PARTICLE = 28;

    // spare parts
    /**
     * @var int
     */
    public const BUILDING_FUNCTION_FABRICATION_HALL = 29;

    /**
     * @var int
     */
    public const BUILDING_FUNCTION_TECH_CENTER = 30;

    /**
     * @var int
     */
    public const SHIELD_GENERATOR_CAPACITY = 4000;

    /**
     * @var int
     */
    public const SHIELD_BATTERY_CAPACITY = 10000;
}
