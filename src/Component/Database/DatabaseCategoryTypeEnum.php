<?php

declare(strict_types=1);

namespace Stu\Component\Database;

enum DatabaseCategoryTypeEnum: int
{
    case SHIPRUMP = 1;
    case RPGSHIP = 2;
    case TRADEPOST = 3;
    case REGION = 4;
    case COLONY_CLASS = 5;
    case STAR_SYSTEM_TYPE = 6;
    case STARSYSTEM = 7;
    case STATIONRUMP = 9;
}
