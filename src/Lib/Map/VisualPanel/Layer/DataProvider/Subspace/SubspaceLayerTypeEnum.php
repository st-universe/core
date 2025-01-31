<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace;

enum SubspaceLayerTypeEnum: int
{
    case ALL = 0;
    case IGNORE_USER = 1;
    case SPACECRAFT_ONLY = 2;
    case USER_ONLY = 3;
    case ALLIANCE_ONLY = 4;
}
