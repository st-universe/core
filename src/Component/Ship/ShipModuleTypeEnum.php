<?php

declare(strict_types=1);

namespace Stu\Component\Ship;

use Stu\Component\Ship\System\ShipSystemTypeEnum;

final class ShipModuleTypeEnum
{
    // standard module types
    public const MODULE_TYPE_HULL = 1;
    public const MODULE_TYPE_SHIELDS = 2;
    public const MODULE_TYPE_EPS = 3;
    public const MODULE_TYPE_IMPULSEDRIVE = 4;
    public const MODULE_TYPE_REACTOR = 5;
    public const MODULE_TYPE_COMPUTER = 6;
    public const MODULE_TYPE_PHASER = 7;
    public const MODULE_TYPE_TORPEDO = 8;
    public const MODULE_TYPE_SPECIAL = 9;
    public const MODULE_TYPE_SENSOR = 10;
    public const MODULE_TYPE_WARPDRIVE = 11;

    public const STANDARD_MODULE_TYPE_COUNT = 11;

    // specific module ids
    public const MODULE_ID_FUSIONREACTOR = 11501;

    //mandatory types
    public const MODULE_OPTIONAL = 0;
    public const MODULE_MANDATORY = 1;

    public const MODULE_TYPE_TO_SYSTEM_TYPE = [
        ShipModuleTypeEnum::MODULE_TYPE_SHIELDS => ShipSystemTypeEnum::SYSTEM_SHIELDS,
        ShipModuleTypeEnum::MODULE_TYPE_WARPDRIVE => ShipSystemTypeEnum::SYSTEM_WARPDRIVE,
        ShipModuleTypeEnum::MODULE_TYPE_EPS => ShipSystemTypeEnum::SYSTEM_EPS,
        ShipModuleTypeEnum::MODULE_TYPE_IMPULSEDRIVE => ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE,
        ShipModuleTypeEnum::MODULE_TYPE_REACTOR => ShipSystemTypeEnum::SYSTEM_WARPCORE,
        ShipModuleTypeEnum::MODULE_TYPE_COMPUTER => ShipSystemTypeEnum::SYSTEM_COMPUTER,
        ShipModuleTypeEnum::MODULE_TYPE_PHASER => ShipSystemTypeEnum::SYSTEM_PHASER,
        ShipModuleTypeEnum::MODULE_TYPE_TORPEDO => ShipSystemTypeEnum::SYSTEM_TORPEDO,
        ShipModuleTypeEnum::MODULE_TYPE_SENSOR => ShipSystemTypeEnum::SYSTEM_LSS
    ];
}
