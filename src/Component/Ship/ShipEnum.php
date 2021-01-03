<?php

declare(strict_types=1);

namespace Stu\Component\Ship;

use Stu\Module\Commodity\CommodityTypeEnum;

final class ShipEnum
{
    //fly stuff
    public const FLY_RIGHT = 1;
    public const FLY_LEFT = 2;
    public const FLY_UP = 3;
    public const FLY_DOWN = 4;

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
    public const WARPCORE_LOAD = 20;
    public const WARPCORE_LOAD_COST = [
        CommodityTypeEnum::GOOD_DEUTERIUM => 2,
        CommodityTypeEnum::GOOD_ANTIMATTER => 2,
        CommodityTypeEnum::GOOD_DILITHIUM => 1
    ];
    public const WARPCORE_CAPACITY_MULTIPLIER = 15;

    //other
    public const TRUMFIELD_CLASS = 8;
    public const SHIELD_REGENERATION_TIME = 900;
    public const SHIP_CATEGORY_DEBRISFIELD = 7;
    public const SHIP_CATEGORY_ESCAPE_PODS = 9;
}
