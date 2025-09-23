<?php

declare(strict_types=1);

namespace Stu\Component\Ship\Wormhole;

enum WormholeEntryTypeEnum: int
{
    case USER = 1;
    case ALLIANCE = 2;
    case FACTION = 3;
    case SHIP = 4;
}
