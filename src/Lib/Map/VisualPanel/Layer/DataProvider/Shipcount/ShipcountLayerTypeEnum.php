<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider\Shipcount;

enum ShipcountLayerTypeEnum: int
{
    case ALL = 0;
    case USER_ONLY = 2;
    case ALLIANCE_ONLY = 3;
}
