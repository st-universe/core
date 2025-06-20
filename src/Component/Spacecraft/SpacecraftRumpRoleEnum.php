<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft;

enum SpacecraftRumpRoleEnum: int
{
    // rump roles
    case SHIP_ROLE_PHASER_SHIP = 1;
    case SHIP_ROLE_PULSE_SHIP = 2;
    case SHIP_ROLE_TORPEDO_SHIP = 3;
    case SHIP_ROLE_RESEARCH_VESSEL = 4;
    case SHIP_ROLE_COLONIZER = 5;
    case SHIP_ROLE_GREAT_FREIGHTER = 6;
    case SHIP_ROLE_LONGRANGE_FREIGHTER = 7;
    case SHIP_ROLE_SHORTRANGE_FREIGHTER = 8;
    case SHIP_ROLE_SHUTTLE = 9;
    case SHIP_ROLE_CONSTRUCTION = 10;
    case SHIP_ROLE_DEPOT_SMALL = 11;
    case SHIP_ROLE_DEPOT_LARGE = 12;
    case SHIP_ROLE_SHIPYARD = 13;
    case SHIP_ROLE_SENSOR = 14;
    case SHIP_ROLE_OUTPOST = 15;
    case SHIP_ROLE_BASE = 16;
    case SHIP_ROLE_ADVENT_DOOR = 17;
    case SHIP_ROLE_THOLIAN_WEB = 18;
}
