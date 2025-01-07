<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\DataProvider\Spacecraftcount;

enum SpacecraftCountLayerTypeEnum: int
{
    case ALL = 0;
    case SPACECRAFT_ONLY = 1;
    case USER_ONLY = 2;
    case ALLIANCE_ONLY = 3;
}
