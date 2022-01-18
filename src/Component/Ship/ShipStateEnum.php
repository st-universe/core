<?php

declare(strict_types=1);

namespace Stu\Component\Ship;

final class ShipStateEnum
{

    public const SHIP_STATE_NONE = 0;
    public const SHIP_STATE_REPAIR_PASSIVE = 1;
    public const SHIP_STATE_SYSTEM_MAPPING = 2;
    public const SHIP_STATE_UNDER_CONSTRUCTION = 3;
    public const SHIP_STATE_REPAIR_ACTIVE = 4;
}
