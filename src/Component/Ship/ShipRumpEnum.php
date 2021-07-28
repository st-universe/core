<?php

declare(strict_types=1);

namespace Stu\Component\Ship;

final class ShipRumpEnum
{
    // rump categories
    public const SHIP_CATEGORY_DEBRISFIELD = 7;
    public const SHIP_CATEGORY_TRUMFIELD = 8;
    public const SHIP_CATEGORY_ESCAPE_PODS = 9;
    public const SHIP_CATEGORY_SHUTTLE = 10;
    public const SHIP_CATEGORY_CONSTRUCTION = 11;
    public const SHIP_CATEGORY_STATION = 12;

    // rump roles TODO
    public const SHIP_ROLE_SENSOR = 12;
    public const SHIP_ROLE_DEPOT_SMALL = 12;
    public const SHIP_ROLE_DEPOT_LARGE = 12;
    public const SHIP_ROLE_BASE_LARGE = 12;

    // rump offsets
    public const SHIP_RUMP_BASE_ID_ESCAPE_PODS = 100;
    public const SHIP_RUMP_BASE_ID_CONSTRUCTION = 10000;
}
