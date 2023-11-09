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
    public const SYSTEM_ECOST_DOCK = 1;

    //damage stuff
    public const DAMAGE_MODE_HULL = 1;
    public const DAMAGE_MODE_SHIELDS = 2;
    public const DAMAGE_TYPE_PHASER = 1;
    public const DAMAGE_TYPE_TORPEDO = 2;

    //flight and signature directions
    public const DIRECTION_LEFT = 1;
    public const DIRECTION_BOTTOM = 2;
    public const DIRECTION_RIGHT = 3;
    public const DIRECTION_TOP = 4;

    //other
    public const SHIELD_REGENERATION_TIME = 900;

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
