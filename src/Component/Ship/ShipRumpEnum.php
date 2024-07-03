<?php

declare(strict_types=1);

namespace Stu\Component\Ship;

final class ShipRumpEnum
{
    // rump categories
    public const int SHIP_CATEGORY_DEBRISFIELD = 7;
    public const int SHIP_CATEGORY_TRUMFIELD = 8;
    public const int SHIP_CATEGORY_ESCAPE_PODS = 9;
    public const int SHIP_CATEGORY_SHUTTLE = 10;
    public const int SHIP_CATEGORY_CONSTRUCTION = 11;
    public const int SHIP_CATEGORY_STATION = 12;

    // rump roles
    public const int SHIP_ROLE_CONSTRUCTION = 10;
    public const int SHIP_ROLE_DEPOT_SMALL = 11;
    public const int SHIP_ROLE_DEPOT_LARGE = 12;
    public const int SHIP_ROLE_SHIPYARD = 13;
    public const int SHIP_ROLE_SENSOR = 14;
    public const int SHIP_ROLE_OUTPOST = 15;
    public const int SHIP_ROLE_BASE = 16;

    // rump offsets
    public const int SHIP_RUMP_BASE_ID_ESCAPE_PODS = 100;
    public const int SHIP_RUMP_BASE_ID_CONSTRUCTION = 10000;

    /**
     * @deprecated but kept for documentation reasons
     */
    public const int SHIP_ROLE_ADVENT_DOOR = 17;
}
