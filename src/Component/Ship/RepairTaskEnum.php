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
}
