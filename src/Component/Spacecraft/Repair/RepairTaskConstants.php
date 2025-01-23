<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\Repair;

final class RepairTaskConstants
{
    // repair part usage
    public const int SPARE_PARTS_ONLY = 1;
    public const int SYSTEM_COMPONENTS_ONLY = 2;
    public const int BOTH = 3;

    // repair percentages absolut
    public const int SPARE_PARTS_ONLY_MIN = 13;
    public const int SPARE_PARTS_ONLY_MAX = 17;

    public const int SYSTEM_COMPONENTS_ONLY_MIN = 22;
    public const int SYSTEM_COMPONENTS_ONLY_MAX = 28;

    public const int BOTH_MIN = 35;
    public const int BOTH_MAX = 45;

    // repair duration
    public const int STANDARD_REPAIR_DURATION = 10800; //3 hours

    // costs for hull
    public const int HULL_HITPOINTS_PER_SPARE_PART = 100;

    // repair parts for shipyards per module level
    public const array SHIPYARD_PARTS_USAGE = [
        1 => [self::SPARE_PARTS_ONLY => 1, self::SYSTEM_COMPONENTS_ONLY => 0],
        2 => [self::SPARE_PARTS_ONLY => 2, self::SYSTEM_COMPONENTS_ONLY => 0],
        3 => [self::SPARE_PARTS_ONLY => 4, self::SYSTEM_COMPONENTS_ONLY => 1],
        4 => [self::SPARE_PARTS_ONLY => 5, self::SYSTEM_COMPONENTS_ONLY => 3],
        5 => [self::SPARE_PARTS_ONLY => 6, self::SYSTEM_COMPONENTS_ONLY => 5],
        6 => [self::SPARE_PARTS_ONLY => 8, self::SYSTEM_COMPONENTS_ONLY => 8]
    ];
}
