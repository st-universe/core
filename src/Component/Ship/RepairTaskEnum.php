<?php

declare(strict_types=1);

namespace Stu\Component\Ship;

final class RepairTaskEnum
{
    // repair part usage
    public const SPARE_PARTS_ONLY = 1;
    public const SYSTEM_COMPONENTS_ONLY = 2;
    public const BOTH = 3;

    // repair percentages absolut
    public const SPARE_PARTS_ONLY_MIN = 13;
    public const SPARE_PARTS_ONLY_MAX = 17;

    public const SYSTEM_COMPONENTS_ONLY_MIN = 22;
    public const SYSTEM_COMPONENTS_ONLY_MAX = 28;

    public const BOTH_MIN = 35;
    public const BOTH_MAX = 45;

    // repair duration
    public const STANDARD_REPAIR_DURATION = 10800; //3 hours

    // costs for hull
    public const HULL_HITPOINTS_PER_SPARE_PART = 100;

    // repair parts for shipyards per module level
    public const SHIPYARD_PARTS_USAGE = [
        1 => [self::SPARE_PARTS_ONLY => 2, self::SYSTEM_COMPONENTS_ONLY => 0],
        2 => [self::SPARE_PARTS_ONLY => 4, self::SYSTEM_COMPONENTS_ONLY => 0],
        3 => [self::SPARE_PARTS_ONLY => 6, self::SYSTEM_COMPONENTS_ONLY => 1],
        4 => [self::SPARE_PARTS_ONLY => 8, self::SYSTEM_COMPONENTS_ONLY => 3],
        5 => [self::SPARE_PARTS_ONLY => 9, self::SYSTEM_COMPONENTS_ONLY => 10],
        6 => [self::SPARE_PARTS_ONLY => 10, self::SYSTEM_COMPONENTS_ONLY => 20],
    ];
}
