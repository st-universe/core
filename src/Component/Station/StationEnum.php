<?php

declare(strict_types=1);

namespace Stu\Component\Station;

use Stu\Component\Spacecraft\SpacecraftRumpEnum;

final class StationEnum
{
    // where can the station type be build
    public const string BUILDABLE_EVERYWHERE = 'überall';
    public const string BUILDABLE_OVER_SYSTEM = 'über einem System';
    public const string BUILDABLE_INSIDE_SYSTEM = 'innerhalb eines Systems';
    public const string BUILDABLE_OUTSIDE_SYSTEM = 'außerhalb eines Systems';

    // station role buildable locations
    public const array BUILDABLE_LOCATIONS_PER_ROLE = [
        SpacecraftRumpEnum::SHIP_ROLE_SHIPYARD => self::BUILDABLE_INSIDE_SYSTEM,
        SpacecraftRumpEnum::SHIP_ROLE_SENSOR => self::BUILDABLE_EVERYWHERE,
        SpacecraftRumpEnum::SHIP_ROLE_DEPOT_LARGE => self::BUILDABLE_INSIDE_SYSTEM,
        SpacecraftRumpEnum::SHIP_ROLE_BASE => self::BUILDABLE_OVER_SYSTEM,
        SpacecraftRumpEnum::SHIP_ROLE_OUTPOST => self::BUILDABLE_OUTSIDE_SYSTEM,
        SpacecraftRumpEnum::SHIP_ROLE_DEPOT_SMALL => self::BUILDABLE_INSIDE_SYSTEM
    ];

    // buildable limits
    public const array BUILDABLE_LIMITS_PER_ROLE = [
        SpacecraftRumpEnum::SHIP_ROLE_CONSTRUCTION => 2,
        SpacecraftRumpEnum::SHIP_ROLE_SHIPYARD => 2,
        SpacecraftRumpEnum::SHIP_ROLE_SENSOR => PHP_INT_MAX,
        SpacecraftRumpEnum::SHIP_ROLE_DEPOT_LARGE => 1,
        SpacecraftRumpEnum::SHIP_ROLE_BASE => PHP_INT_MAX,
        SpacecraftRumpEnum::SHIP_ROLE_OUTPOST => PHP_INT_MAX,
        SpacecraftRumpEnum::SHIP_ROLE_DEPOT_SMALL => PHP_INT_MAX
    ];
}
