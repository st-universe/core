<?php

declare(strict_types=1);

namespace Stu\Component\Ship;

use Stu\Module\Commodity\CommodityTypeEnum;

final class ShipEnum
{
    //dock stuff
    public const DOCK_PRIVILEGE_MODE_ALLOW = 1;
    public const DOCK_PRIVILEGE_MODE_DENY = 2;
    public const DOCK_PRIVILEGE_USER = 1;
    public const DOCK_PRIVILEGE_ALLIANCE = 2;
    public const DOCK_PRIVILEGE_FACTION = 3;

    //damage stuff
    public const DAMAGE_MODE_HULL = 1;
    public const DAMAGE_MODE_SHIELDS = 2;
    public const DAMAGE_TYPE_PHASER = 1;
    public const DAMAGE_TYPE_TORPEDO = 2;

    //warpcore stuff
    public const WARPCORE_LOAD = 30;
    public const WARPCORE_LOAD_COST = [
        CommodityTypeEnum::COMMODITY_DILITHIUM => 1,
        CommodityTypeEnum::COMMODITY_ANTIMATTER => 2,
        CommodityTypeEnum::COMMODITY_DEUTERIUM => 2
    ];
    public const WARPCORE_CAPACITY_MULTIPLIER = 15;

    //reactor stuff
    public const REACTOR_LOAD = 2;
    public const REACTOR_LOAD_COST = [
        CommodityTypeEnum::COMMODITY_DEUTERIUM => 1
    ];
    public const REACTOR_CAPACITY_MULTIPLIER = 10;

    //flight and signature directions
    public const DIRECTION_LEFT = 1;
    public const DIRECTION_BOTTOM = 2;
    public const DIRECTION_RIGHT = 3;
    public const DIRECTION_TOP = 4;

    //other
    public const SHIELD_REGENERATION_TIME = 900;
    public const TURNS_TO_TAKEOVER = 10;

    //First Coloship
    public const FED_COL_RUMP = 6501;
    public const ROM_COL_RUMP = 6502;
    public const KLING_COL_RUMP = 6503;
    public const CARD_COL_RUMP = 6504;
    public const FERG_COL_RUMP = 6505;

    public const FED_COL_BUILDPLAN = 2075;
    public const ROM_COL_BUILDPLAN = 2076;
    public const KLING_COL_BUILDPLAN = 2077;
    public const CARD_COL_BUILDPLAN = 2078;
    public const FERG_COL_BUILDPLAN = 2079;
}
