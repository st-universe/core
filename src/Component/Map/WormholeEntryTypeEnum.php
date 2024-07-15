<?php

declare(strict_types=1);

namespace Stu\Component\Map;

enum WormholeEntryTypeEnum: string
{
    case MAP_TO_W = 'MAP -> W';
    case W_TO_MAP = 'W -> MAP';
    case BOTH = 'MAP <-> W';
}
