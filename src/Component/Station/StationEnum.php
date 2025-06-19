<?php

declare(strict_types=1);

namespace Stu\Component\Station;

use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;

final class StationEnum
{
    // where can the station type be build
    public const string BUILDABLE_EVERYWHERE = 'überall';
    public const string BUILDABLE_OVER_SYSTEM = 'über einem System';
    public const string BUILDABLE_INSIDE_SYSTEM = 'innerhalb eines Systems';
    public const string BUILDABLE_OUTSIDE_SYSTEM = 'außerhalb eines Systems';

    // station role buildable locations
    public const array BUILDABLE_LOCATIONS_PER_ROLE = [
        SpacecraftRumpRoleEnum::SHIP_ROLE_SHIPYARD->value => self::BUILDABLE_INSIDE_SYSTEM,
        SpacecraftRumpRoleEnum::SHIP_ROLE_SENSOR->value => self::BUILDABLE_EVERYWHERE,
        SpacecraftRumpRoleEnum::SHIP_ROLE_DEPOT_LARGE->value => self::BUILDABLE_INSIDE_SYSTEM,
        SpacecraftRumpRoleEnum::SHIP_ROLE_BASE->value => self::BUILDABLE_OVER_SYSTEM,
        SpacecraftRumpRoleEnum::SHIP_ROLE_OUTPOST->value => self::BUILDABLE_OUTSIDE_SYSTEM,
        SpacecraftRumpRoleEnum::SHIP_ROLE_DEPOT_SMALL->value => self::BUILDABLE_INSIDE_SYSTEM
    ];

    // buildable limits
    public const array BUILDABLE_LIMITS_PER_ROLE = [
        SpacecraftRumpRoleEnum::SHIP_ROLE_CONSTRUCTION->value => 2,
        SpacecraftRumpRoleEnum::SHIP_ROLE_SHIPYARD->value => 2,
        SpacecraftRumpRoleEnum::SHIP_ROLE_SENSOR->value => PHP_INT_MAX,
        SpacecraftRumpRoleEnum::SHIP_ROLE_DEPOT_LARGE->value => 1,
        SpacecraftRumpRoleEnum::SHIP_ROLE_BASE->value => PHP_INT_MAX,
        SpacecraftRumpRoleEnum::SHIP_ROLE_OUTPOST->value => PHP_INT_MAX,
        SpacecraftRumpRoleEnum::SHIP_ROLE_DEPOT_SMALL->value => PHP_INT_MAX
    ];
}
