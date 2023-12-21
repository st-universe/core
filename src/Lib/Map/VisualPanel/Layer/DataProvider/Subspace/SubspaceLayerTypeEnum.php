<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace;

enum SubspaceLayerTypeEnum: int
{
    case ALL = 0;
    case IGNORE_USER = 1;
    case USER_ONLY = 2;
    case ALLIANCE_ONLY = 3;
}
