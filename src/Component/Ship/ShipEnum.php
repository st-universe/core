<?php

declare(strict_types=1);

namespace Stu\Component\Ship;

final class ShipEnum
{

    public const FLY_RIGHT = 1;
    public const FLY_LEFT = 2;
    public const FLY_UP = 3;
    public const FLY_DOWN = 4;
    public const DOCK_PRIVILEGE_MODE_ALLOW = 1;
    public const DOCK_PRIVILEGE_MODE_DENY = 2;
    public const DOCK_PRIVILEGE_USER = 1;
    public const DOCK_PRIVILEGE_ALLIANCE = 2;
    public const DOCK_PRIVILEGE_FACTION = 3;
    public const DAMAGE_MODE_HULL = 1;
    public const DAMAGE_MODE_SHIELDS = 2;
    public const DAMAGE_TYPE_PHASER = 1;
    public const DAMAGE_TYPE_TORPEDO = 2;
    public const WARPCORE_LOAD = 20;
    public const WARPCORE_CAPACITY_MULTIPLIER = 15;
    public const TRUMFIELD_CLASS = 8;
    public const SHIELD_REGENERATION_TIME = 900;
    public const SHIP_CATEGORY_DEBRISFIELD = 7;
}
