<?php

declare(strict_types=1);

namespace Stu\Component\Station\Dock;

enum DockTypeEnum: int
{
    case USER = 1;
    case ALLIANCE = 2;
    case FACTION = 3;
    case SHIP = 4;
}
