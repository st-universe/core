<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft;

use Stu\Component\Station\StationLocationEnum;

enum SpacecraftRumpRoleEnum: int
{
    // rump roles
    case PHASER_SHIP = 1;
    case PULSE_SHIP = 2;
    case TORPEDO_SHIP = 3;
    case RESEARCH_VESSEL = 4;
    case COLONIZER = 5;
    case GREAT_FREIGHTER = 6;
    case LONGRANGE_FREIGHTER = 7;
    case SHORTRANGE_FREIGHTER = 8;
    case SHUTTLE = 9;
    case CONSTRUCTION = 10;
    case DEPOT_SMALL = 11;
    case DEPOT_LARGE = 12;
    case SHIPYARD = 13;
    case SENSOR = 14;
    case OUTPOST = 15;
    case BASE = 16;
    case ADVENT_DOOR = 17;
    case THOLIAN_WEB = 18;


    // buildable limits
    public function getBuildLimit(): int
    {
        return match ($this) {
            self::CONSTRUCTION,
            self::SHIPYARD => 2,
            self::DEPOT_LARGE => 1,
            default => PHP_INT_MAX
        };
    }

    public function getPossibleBuildLocations(): StationLocationEnum
    {
        return match ($this) {
            self::SHIPYARD,
            self::DEPOT_LARGE,
            self::DEPOT_SMALL => StationLocationEnum::BUILDABLE_INSIDE_SYSTEM,
            self::BASE => StationLocationEnum::BUILDABLE_OVER_SYSTEM,
            self::OUTPOST => StationLocationEnum::BUILDABLE_OUTSIDE_SYSTEM,
            default => StationLocationEnum::BUILDABLE_EVERYWHERE
        };
    }
}
