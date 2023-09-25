<?php

declare(strict_types=1);

namespace Stu\Component\Ship;

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
}
